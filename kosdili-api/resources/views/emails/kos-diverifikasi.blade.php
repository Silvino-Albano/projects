<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Status Kos — KosDili</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Segoe UI',Arial,sans-serif;background:#f4efe8;color:#1a1a1a;padding:24px 16px;}
    .wrapper{max-width:560px;margin:0 auto;}
    .header{background:#1e1a14;border-radius:16px 16px 0 0;padding:28px 32px;text-align:center;}
    .brand{font-size:22px;font-weight:700;color:#c17f3e;letter-spacing:1px;}
    .brand-sub{font-size:12px;color:#7a6e65;letter-spacing:2px;text-transform:uppercase;margin-top:4px;}
    .banner-approve{background:#d1fae5;padding:20px 32px;text-align:center;}
    .banner-reject{background:#fee2e2;padding:20px 32px;text-align:center;}
    .banner-icon{font-size:2.8rem;margin-bottom:8px;}
    .banner-title-ok{font-size:20px;font-weight:700;color:#065f46;}
    .banner-title-no{font-size:20px;font-weight:700;color:#991b1b;}
    .banner-desc-ok{font-size:13.5px;color:#047857;line-height:1.6;}
    .banner-desc-no{font-size:13.5px;color:#b91c1c;line-height:1.6;}
    .body{background:#fff;padding:28px 32px;}
    .greeting{font-size:15px;color:#4a3f35;margin-bottom:20px;line-height:1.7;}
    .detail-box{background:#faf8f4;border:1px solid #e8e2d9;border-radius:12px;overflow:hidden;margin-bottom:20px;}
    .detail-head{background:#1e1a14;padding:10px 16px;font-size:11px;font-weight:600;color:#c17f3e;text-transform:uppercase;letter-spacing:1px;}
    .detail-row{display:flex;justify-content:space-between;padding:10px 16px;border-bottom:1px solid #f0ebe3;font-size:13.5px;}
    .detail-row:last-child{border:none;}
    .dl{color:#8a7968;}.dv{font-weight:500;color:#1a1a1a;}
    .catatan-box{background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#78350f;line-height:1.6;}
    .catatan-label{font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;}
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

  @if($status === 'aktif')
  <div class="banner-approve">
    <div class="banner-icon">✅</div>
    <div class="banner-title-ok">Kos Anda Disetujui!</div>
    <div class="banner-desc-ok">Kos Anda sudah aktif dan bisa dilihat oleh pencari kos.</div>
  </div>
  @else
  <div class="banner-reject">
    <div class="banner-icon">❌</div>
    <div class="banner-title-no">Kos Anda Ditolak</div>
    <div class="banner-desc-no">Maaf, kos Anda belum bisa ditampilkan saat ini.</div>
  </div>
  @endif

  <div class="body">
    <p class="greeting">
      Halo, <strong>{{ $namaOwner }}</strong>! 👋<br><br>
      @if($status === 'aktif')
        Kabar baik! Kos <strong>{{ $namaKos }}</strong> Anda telah diverifikasi dan sekarang
        tampil di platform KosDili. Pencari kos sudah bisa melihat dan melakukan booking.
      @else
        Kos <strong>{{ $namaKos }}</strong> Anda belum dapat disetujui oleh admin.
        Silakan periksa catatan di bawah dan perbaiki informasi kos Anda.
      @endif
    </p>

    <div class="detail-box">
      <div class="detail-head">📋 Detail Kos</div>
      <div class="detail-row"><span class="dl">Nama Kos</span><span class="dv">{{ $namaKos }}</span></div>
      <div class="detail-row"><span class="dl">Alamat</span><span class="dv">{{ $alamat }}</span></div>
      <div class="detail-row">
        <span class="dl">Status</span>
        <span class="dv" style="color:{{ $status === 'aktif' ? '#059669' : '#dc2626' }}">
          {{ $status === 'aktif' ? '✅ Aktif' : '❌ Ditolak' }}
        </span>
      </div>
    </div>

    @if($catatan)
    <div class="catatan-box">
      <div class="catatan-label">📝 Catatan dari Admin</div>
      {{ $catatan }}
    </div>
    @endif

    <div class="cta-wrap">
      <a href="{{ $linkDash }}" class="cta-btn">
        {{ $status === 'aktif' ? 'Lihat Dashboard' : 'Perbaiki & Kirim Ulang' }}
      </a>
    </div>
  </div>

  <div class="footer">
    <p>Email otomatis dari <strong style="color:#c17f3e;">KosDili</strong>.<br>
    © {{ date('Y') }} KosDili · Dili, Timor-Leste</p>
  </div>
</div>
</body>
</html>
