<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Status Booking KosDili</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f4efe8;
      color: #1a1a1a;
      padding: 24px 16px;
    }
    .wrapper {
      max-width: 560px;
      margin: 0 auto;
    }

    /* Header */
    .header {
      background: #1e1a14;
      border-radius: 16px 16px 0 0;
      padding: 28px 32px;
      text-align: center;
    }
    .brand {
      font-size: 22px;
      font-weight: 700;
      color: #c17f3e;
      letter-spacing: 1px;
      margin-bottom: 4px;
    }
    .brand-sub {
      font-size: 12px;
      color: #7a6e65;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    /* Status banner */
    .status-banner {
      padding: 20px 32px;
      text-align: center;
    }
    .status-banner.disetujui { background: #d1fae5; }
    .status-banner.ditolak   { background: #fee2e2; }
    .status-icon  { font-size: 2.8rem; margin-bottom: 8px; }
    .status-title { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
    .status-banner.disetujui .status-title { color: #065f46; }
    .status-banner.ditolak   .status-title { color: #991b1b; }
    .status-desc  { font-size: 13.5px; line-height: 1.6; }
    .status-banner.disetujui .status-desc { color: #047857; }
    .status-banner.ditolak   .status-desc { color: #b91c1c; }

    /* Body */
    .body {
      background: #ffffff;
      padding: 28px 32px;
    }
    .greeting {
      font-size: 15px;
      color: #4a3f35;
      margin-bottom: 20px;
      line-height: 1.6;
    }
    .greeting strong { color: #1a1a1a; }

    /* Detail box */
    .detail-box {
      background: #faf8f4;
      border: 1px solid #e8e2d9;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 24px;
    }
    .detail-box-head {
      background: #1e1a14;
      padding: 10px 16px;
      font-size: 11px;
      font-weight: 600;
      color: #c17f3e;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .detail-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 16px;
      border-bottom: 1px solid #f0ebe3;
      font-size: 13.5px;
    }
    .detail-row:last-child { border: none; }
    .detail-label { color: #8a7968; }
    .detail-val   { font-weight: 500; color: #1a1a1a; text-align: right; }
    .detail-val.harga { color: #c17f3e; font-size: 15px; font-weight: 700; }

    /* Next steps */
    .next-steps {
      margin-bottom: 24px;
    }
    .next-title {
      font-size: 13px;
      font-weight: 600;
      color: #6b5b47;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: 12px;
    }
    .step-item {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 10px;
      font-size: 13.5px;
      color: #4a3f35;
      line-height: 1.5;
    }
    .step-num {
      min-width: 22px;
      height: 22px;
      border-radius: 50%;
      background: #c17f3e;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 1px;
    }

    /* Kontak pemilik */
    .owner-box {
      background: #fdf5ec;
      border: 1px solid #f0d9bb;
      border-radius: 10px;
      padding: 14px 16px;
      margin-bottom: 24px;
      font-size: 13px;
      color: #6b5b47;
    }
    .owner-box strong { color: #1a1a1a; display: block; margin-bottom: 4px; font-size: 14px; }

    /* Ditolak — alasan */
    .ditolak-note {
      background: #fff7ed;
      border: 1px solid #fed7aa;
      border-radius: 10px;
      padding: 14px 16px;
      margin-bottom: 24px;
      font-size: 13px;
      color: #92400e;
      line-height: 1.6;
    }

    /* CTA button */
    .cta-wrap { text-align: center; margin-bottom: 24px; }
    .cta-btn {
      display: inline-block;
      background: #c17f3e;
      color: #ffffff !important;
      text-decoration: none;
      padding: 13px 32px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: .3px;
    }

    /* Footer */
    .footer {
      background: #1e1a14;
      border-radius: 0 0 16px 16px;
      padding: 20px 32px;
      text-align: center;
    }
    .footer p { font-size: 11.5px; color: #7a6e65; line-height: 1.8; }
    .footer a  { color: #c17f3e; text-decoration: none; }
  </style>
</head>
<body>
<div class="wrapper">

  <!-- Header -->
  <div class="header">
    <div class="brand">KosDili</div>
    <div class="brand-sub">Dili · Timor-Leste</div>
  </div>

  <!-- Status banner -->
  @if($status === 'confirmed')
  <div class="status-banner disetujui">
    <div class="status-icon">✅</div>
    <div class="status-title">Booking Anda Disetujui!</div>
    <div class="status-desc">Pemilik kos telah mengkonfirmasi permintaan booking Anda.</div>
  </div>
  @else
  <div class="status-banner ditolak">
    <div class="status-icon">❌</div>
    <div class="status-title">Booking Anda Ditolak</div>
    <div class="status-desc">Maaf, pemilik kos tidak dapat menerima booking Anda saat ini.</div>
  </div>
  @endif

  <!-- Body -->
  <div class="body">

    <p class="greeting">
      Halo, <strong>{{ $namaUser }}</strong>! 👋<br><br>
      @if($status === 'confirmed')
        Selamat! Permintaan booking kos Anda telah <strong>disetujui</strong> oleh pemilik.
        Berikut detail booking Anda:
      @else
        Kami informasikan bahwa permintaan booking kos Anda <strong>tidak dapat disetujui</strong>
        oleh pemilik. Anda dapat mencari kos lain yang tersedia di platform kami.
      @endif
    </p>

    <!-- Detail booking -->
    <div class="detail-box">
      <div class="detail-box-head">📋 Detail Booking</div>
      <div class="detail-row">
        <span class="detail-label">🏠 Nama Kos</span>
        <span class="detail-val">{{ $namaKos }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">📍 Alamat</span>
        <span class="detail-val">{{ $alamatKos }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">🚪 Nomor Kamar</span>
        <span class="detail-val">Kamar {{ $nomorKamar }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">📅 Tanggal Masuk</span>
        <span class="detail-val">{{ $tanggalMasuk }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">📅 Tanggal Keluar</span>
        <span class="detail-val">{{ $tanggalKeluar }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">💳 Metode Bayar</span>
        <span class="detail-val">{{ $metodeBayar }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">💰 Total</span>
        <span class="detail-val harga">{{ $totalHarga }}</span>
      </div>
    </div>

    @if($status === 'confirmed')

    <!-- Langkah selanjutnya -->
    <div class="next-steps">
      <div class="next-title">Langkah Selanjutnya</div>
      @if($booking->payment_method === 'cash')
      <div class="step-item">
        <div class="step-num">1</div>
        <span>Hubungi pemilik kos untuk konfirmasi jadwal pindah masuk.</span>
      </div>
      <div class="step-item">
        <div class="step-num">2</div>
        <span>Datangi lokasi kos dan lakukan pembayaran <strong>cash</strong> langsung kepada pemilik.</span>
      </div>
      <div class="step-item">
        <div class="step-num">3</div>
        <span>Pemilik akan mengkonfirmasi penerimaan pembayaran di aplikasi KosDili.</span>
      </div>
      @else
      <div class="step-item">
        <div class="step-num">1</div>
        <span>Lakukan transfer sesuai rekening yang diberikan pemilik kos.</span>
      </div>
      <div class="step-item">
        <div class="step-num">2</div>
        <span>Upload bukti transfer melalui halaman "Booking Saya" di aplikasi KosDili.</span>
      </div>
      <div class="step-item">
        <div class="step-num">3</div>
        <span>Pemilik akan memverifikasi dan mengkonfirmasi pembayaran Anda.</span>
      </div>
      @endif
    </div>

    <!-- Kontak pemilik -->
    <div class="owner-box">
      <strong>📞 Kontak Pemilik Kos</strong>
      Nama: {{ $namaPemilik }}<br>
      @if($telpPemilik)
      Telepon/WA: <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $telpPemilik) }}" style="color:#c17f3e;">{{ $telpPemilik }}</a>
      @endif
    </div>

    <!-- CTA button -->
    <div class="cta-wrap">
      <a href="{{ config('app.url') }}/owner/bookings" class="cta-btn">
        Lihat Status Booking Saya
      </a>
    </div>

    @else

    <!-- Booking ditolak -->
    <div class="ditolak-note">
      💡 <strong>Jangan khawatir!</strong> Masih banyak kos tersedia di Dili.
      Anda dapat mencari kos lain yang sesuai dengan kebutuhan Anda.
    </div>

    <div class="cta-wrap">
      <a href="{{ config('app.url') }}" class="cta-btn">
        Cari Kos Lainnya
      </a>
    </div>

    @endif

  </div>

  <!-- Footer -->
  <div class="footer">
    <p>
      Email ini dikirim otomatis oleh sistem <strong style="color:#c17f3e;">KosDili</strong>.<br>
      Jangan balas email ini langsung.<br><br>
      © {{ date('Y') }} KosDili · Dili, Timor-Leste
    </p>
  </div>

</div>
</body>
</html>