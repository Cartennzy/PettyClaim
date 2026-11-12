import re
from datetime import datetime
from typing import List, Dict, Any
import cv2
import numpy as np
from fastapi import FastAPI, File, UploadFile
from paddleocr import PaddleOCR

app = FastAPI(title="PettyClaim OCR Microservice", version="1.0")

# Inisialisasi engine OCR (use_angle_cls=True untuk memutar gambar jika struk miring/terbalik)
ocr = PaddleOCR(use_angle_cls=True, lang="en", show_log=False)

def clean_alphanumeric(text: str) -> str:
    return re.sub(r"[^a-zA-Z0-9\s]", "", text).strip()

def parse_amount(text: str) -> float:
    # Hapus Rp, titik, koma, spasi
    cleaned = re.sub(r"rp\.?", "", text, flags=re.IGNORECASE)
    cleaned = cleaned.replace(" ", "").replace(".", "").replace(",", "")
    nums = re.findall(r"\d+", cleaned)
    if nums:
        val = float(nums[-1])
        # Filter batas wajar petty cash (Rp 1.000 s.d. Rp 50.000.000)
        if 1000 <= val <= 50000000:
            return val
    return 0.0

@app.post("/extract-receipt")
async def extract_receipt(file: UploadFile = File(...)):
    # 1. Baca byte gambar dari multipart request
    contents = await file.read()
    nparr = np.frombuffer(contents, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

    if img is None:
        return {"merchant": "STRUK PEMBELIAN", "total": 0.0, "date": str(datetime.now().date()), "category": "1101 - Beban Operasional / Dapur"}

    # 2. Proses OCR melalui PaddleOCR
    ocr_result = ocr.ocr(img, cls=True)

    if not ocr_result or not ocr_result[0]:
        return {"merchant": "STRUK PEMBELIAN", "total": 0.0, "date": str(datetime.now().date()), "category": "1101 - Beban Operasional / Dapur"}

    # 3. Kumpulkan bounding box dan koordinat teks
    boxes: List[Dict[str, Any]] = []
    for line in ocr_result[0]:
        box_coords, (text, confidence) = line
        # Koordinat pojok kiri atas [x, y]
        x1, y1 = box_coords[0]
        # Koordinat pojok kanan bawah [x, y]
        x2, y2 = box_coords[2]
        
        boxes.append({
            "text": text.strip(),
            "x": x1,
            "y": y1,
            "x2": x2,
            "y2": y2,
            "h": y2 - y1,
            "conf": confidence
        })

    # Urutkan urutan teks dari atas ke bawah
    boxes.sort(key=lambda b: b["y"])
    all_raw_text = " ".join([b["text"] for b in boxes]).lower()

    # --- A. DETEKSI NAMA MERCHANT ---
    merchant = "STRUK PEMBELIAN"
    known_brands = [
        "INDOMARET", "ALFAMART", "ALFAMIDI", "PERTAMINA", "SHELL", 
        "SUPERINDO", "HYPERMART", "STARBUCKS", "KFC", "MCDONALD", "GRAMEDIA"
    ]
    
    # Prioritaskan brand ritel besar jika terdapat di teks struk
    found_known = False
    for brand in known_brands:
        if brand.lower() in all_raw_text:
            merchant = brand
            found_known = True
            break

    # Fallback jika bukan ritel umum: ambil baris paling atas yang valid
    if not found_known:
        ignore_merchant_words = [
            "jl", "jalan", "telp", "phone", "nota", "struk", "selamat", 
            "welcome", "kasir", "cashier", "order", "tanggal", "tgl"
        ]
        for b in boxes[:6]:
            clean = clean_alphanumeric(b["text"])
            lower = clean.lower()
            if len(clean) >= 3 and not clean.isdigit():
                if not any(ign in lower for ign in ignore_merchant_words):
                    merchant = clean.upper()
                    break

    # --- B. DETEKSI NOMINAL TOTAL BERDASARKAN KOORDINAT 2D ---
    detected_total = 0.0
    total_keywords = ["total bayar", "grand total", "total akhir", "tagihan", "total", "jumlah"]
    exclude_keywords = ["kembali", "change", "tunai", "cash", "debit", "kartu", "bayar:"]

    for i, b in enumerate(boxes):
        text_lower = b["text"].lower()

        # Lewati baris kembalian / tunai
        if any(ex in text_lower for ex in exclude_keywords) and not any(kw in text_lower for kw in total_keywords):
            continue

        # Cek apakah blok teks mengandung kata kunci TOTAL
        is_total_label = any(kw in text_lower for kw in total_keywords)
        if is_total_label:
            # 1. Cek apakah nominal ada di dalam baris/box teks yang sama
            amt = parse_amount(b["text"])
            if amt > 0:
                detected_total = amt
                break

            # 2. Cari box teks lain yang posisinya horizontal sejajar di sebelah kanan (selisih Y < 25px, X lebih besar)
            horizontal_matches = [
                other for other in boxes 
                if abs(other["y"] - b["y"]) <= (b["h"] * 1.2) and other["x"] > b["x"]
            ]
            horizontal_matches.sort(key=lambda item: item["x"])

            for target in horizontal_matches:
                amt = parse_amount(target["text"])
                if amt > 0:
                    detected_total = amt
                    break
            
            if detected_total > 0:
                break

            # 3. Jika struk vertikal, cek baris tepat di bawahnya (i + 1)
            if i + 1 < len(boxes):
                next_box = boxes[i + 1]
                amt = parse_amount(next_box["text"])
                if amt > 0:
                    detected_total = amt
                    break

    # Fallback: jika keyword gagal ditemukan, ambil angka valid terbesar di paruh bawah struk
    if detected_total == 0.0:
        candidate_amounts = []
        bottom_boxes = boxes[len(boxes)//2:]
        for b in bottom_boxes:
            lower = b["text"].lower()
            if "kembali" in lower or "change" in lower or "tunai" in lower:
                continue
            amt = parse_amount(b["text"])
            if amt > 0:
                candidate_amounts.append(amt)
        if candidate_amounts:
            detected_total = max(candidate_amounts)

    # --- C. DETEKSI TANGGAL ---
    detected_date = str(datetime.now().date())
    for b in boxes:
        # Format DD/MM/YYYY atau DD-MM-YYYY
        m = re.search(r"\b([0-3]?\d)[-/.]([0-1]?\d)[-/.](20\d{2}|\d{2})\b", b["text"])
        if m:
            d, mo, y = m.groups()
            y = f"20{y}" if len(y) == 2 else y
            try:
                valid_date = datetime(int(y), int(mo), int(d))
                detected_date = valid_date.strftime("%Y-%m-%d")
                break
            except ValueError:
                pass

    # --- D. DETEKSI KATEGORI BEBAN ---
    category = "1101 - Beban Operasional / Dapur"
    if re.search(r"(bensin|spbu|pertamina|shell|bp|pertalite|pertamax|solar|parkir|toll)", all_raw_text):
        category = "1103 - Beban Bensin & Transportasi"
    elif re.search(r"(atk|kertas|print|fotocopy|pulpen|lakban|spidol|buku)", all_raw_text):
        category = "1102 - Beban Alat Tulis Kantor (ATK)"
    elif re.search(r"(jne|j&t|sicepat|anteraja|tiki|kurir|ongkir|delivery)", all_raw_text):
        category = "1104 - Beban Kurir & Logistik"
    elif re.search(r"(bengkel|servis|service|oli|ban|sparepart)", all_raw_text):
        category = "1105 - Beban Perbaikan & Pemeliharaan"

    return {
        "merchant": merchant,
        "total": detected_total,
        "date": detected_date,
        "category": category
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="127.0.0.1", port=5000, reload=True)