<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pembayaran Dikonfirmasi — KosDili</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Segoe UI',Arial,sans-serif;background:#f4efe8;color:#1a1a1a;padding:24px 16px;}
    .wrapper{max-width:560px;margin:0 auto;}
    .header{background:#1e1a14;border-radius:16px 16px 0 0;padding:28px 32px;text-align:center;}
    .brand{font-size:22px;font-weight:700;color:#c17f3e;letter-spacing:1px;}
    .brand-sub{font-size:12px;color:#7a6e65;letter-spacing:2px;text-transform:uppercase;margin-top:4px;}
    .banner{background:#d1fae5;padding:20px 32px;text-align:center;}
    .banner-icon{font-size:2.8rem;margin-bottom:8px;}
    .banner-title{font-size:20px;font-weight:700;color:#065f46;margin-bottom:4px;}
    .banner-desc{font-size:13.5px;color:#047857;line-height:1.6;}
    .body{background:#fff;padding:28px 32px;}
    .greeting{font-size:15px;color:#4a3f35;margin-bottom:20px;line-height:1.6;}
    .detail-box{background:#faf8f4;border:1px solid #e8e2d9;border-radius:12px;overflow:hidden;margin-bottom:20px;}
    .detail-head{background:#1e1a14;padding:10px 16px;font-size:11px;font-weight:600;color:#c17f3e;text-transform:uppercase;letter-spacing:1px;}
    .detail-row{display:flex;justify-content:space-between;padding:10px 16px;border-bottom:1px solid #f0ebe3;font-size:13.5px;}
    .detail-row:last-child{border:none;}
    .dl{color:#8a7968;}.dv{font-weight:500;color:#1a1a1a;}
    .dv.harga{color:#c17f3e;font-size:15px;font-weight:700;}
    .success-note{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#166534;line-height:1.6;}
    .cta-wrap{text-align:center;margin-bottom:20px;}
    .cta-btn{display:inline-block;background:#c17f3e;color:#fff!important;text-decoration:none;padding:13px 32px;border-radius:10px;font-size:14px;font-weight:600;}
    .footer{background:#1e1a14;border-radius:0 0 16px 16px;padding:20px 32px;text-align:center;}
    .footer p{font-size:11.5px;color:#7a6e65;line-height:1.8;}
  </style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="brand">KosDili</div>
    <div class="brand-sub">Dili · Timor-Leste</div>
  </div>

  <div class="banner">
    <div class="banner-icon">💵</div>
    <div class="banner-title">Pembayaran Dikonfirmasi!</div>
    <div class="banner-desc">Pemilik kos telah mengkonfirmasi penerimaan pembayaran Anda.</div>
  </div>

  <div class="body">
    <p class="greeting">Halo, <strong>{{ $namaUser }}</strong>! 👋<br><br>
      Pembayaran untuk kos Anda telah <strong>dikonfirmasi</strong> oleh pemilik.
      Status sewa Anda sekarang aktif. Selamat menempati kos!
    </p>

    <div class="detail-box">
      <div class="detail-head">📋 Detail Sewa</div>
      <div class="detail-row"><span class="dl">🏠 Nama Kos</span><span class="dv">{{ $namaKos }}</span></div>
      <div class="detail-row"><span class="dl">🚪 Nomor Kamar</span><span class="dv">Kamar {{ $nomorKamar }}</span></div>
      <div class="detail-row"><span class="dl">📅 Tanggal Masuk</span><span class="dv">{{ $tanggalMasuk }}</span></div>
      <div class="detail-row"><span class="dl">📅 Tanggal Keluar</span><span class="dv">{{ $tanggalKeluar }}</span></div>
      <div class="detail-row"><span class="dl">💰 Total Dibayar</span><span class="dv harga">{{ $totalHarga }}</span></div>
    </div>

    <div class="success-note">
      ✅ Simpan email ini sebagai bukti konfirmasi sewa Anda.<br>
      Jika ada pertanyaan, hubungi pemilik kos langsung.
    </div>

    <div class="cta-wrap">
      <a href="{{ config('app.url') }}" class="cta-btn">Buka KosDili</a>
    </div>
  </div>

  <div class="footer">
    <p>Email ini dikirim otomatis oleh <strong style="color:#c17f3e;">KosDili</strong>.<br>
    Jangan balas email ini.<br>© {{ date('Y') }} KosDili · Dili, Timor-Leste</p>
  </div>
</div>
</body>
</html>