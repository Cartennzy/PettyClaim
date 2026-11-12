<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 20px; }
        .card { background-color: #1e293b; border-radius: 12px; padding: 24px; max-width: 500px; margin: 0 auto; border: 1px solid #334155; }
        .title { font-size: 20px; font-weight: bold; color: #ffffff; margin-bottom: 8px; }
        .subtitle { font-size: 13px; color: #94a3b8; margin-bottom: 20px; }
        .info-box { background-color: #0f172a; border-radius: 8px; padding: 16px; margin-bottom: 20px; border: 1px solid #334155; }
        .info-item { font-size: 13px; margin-bottom: 8px; }
        .info-label { color: #94a3b8; font-weight: bold; }
        .info-value { color: #38bdf8; font-family: monospace; }
        .footer { font-size: 11px; color: #64748b; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="title">Selamat Datang di PettyClaim!</div>
        <p class="subtitle">Halo <strong>{{ $name }}</strong>, akun Anda telah dibuatkan oleh Tim Finance untuk mengakses sistem pengajuan kas kecil.</p>

        <div class="info-box">
            <div class="info-item">
                <span class="info-label">Email Login:</span>
                <span class="info-value">{{ $email }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Password Sementara:</span>
                <span class="info-value">{{ $password }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Peran (Role):</span>
                <span class="info-value">{{ $role }}</span>
            </div>
        </div>

        <p style="font-size: 12px; color: #cbd5e1;">
            Silakan gunakan kredensial di atas untuk masuk ke dalam aplikasi PettyClaim. Demi keamanan, disarankan untuk memperbarui password Anda setelah login pertama kali.
        </p>

        <div class="footer">
            &copy; {{ date('Y') }} PettyClaim System - Keuangan Kas Kecil Perusahaan
        </div>
    </div>
</body>
</html>