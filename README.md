# PettyClaim — Smart Reimbursement & Petty Cash Management

Sistem informasi pengelolaan klaim *reimbursement* kas kecil perusahaan berbasis *mobile* dan *web* dengan otomatisasi pemindaian struk belanja menggunakan **Optical Character Recognition (OCR)** dan deteksi kecurangan (*AI Fraud Detection*).

## Arsitektur Sistem
1. **Mobile Client (pettyclaim-mobile)**: Flutter (Dart) — Antarmuka Karyawan & Manager, kamera pemindai struk, dan pelacakan status *real-time*.
2. **Backend API & Admin Portal (pettyclaim-backend)**: Laravel (PHP) & PostgreSQL — RESTful API, autentikasi Sanctum, *Audit-First Approval Workflow*, *Auto-Journaling*, dan deteksi duplikasi struk (pHash & MD5).
3. **OCR Microservice (pettyclaim-ocr-service)**: Python 3.11, FastAPI, & PaddleOCR — Layanan ekstraksi teks spasial 2D untuk membaca nama merchant, tanggal transaksi, kategori beban, dan nominal total pada struk kasir.
