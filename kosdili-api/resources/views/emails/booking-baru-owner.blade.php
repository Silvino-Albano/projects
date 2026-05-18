
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Baru Masuk — KosDili</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f4efe8;
      color: #1a1a1a;
      padding: 24px 16px;
      -webkit-font-smoothing: antialiased;
    }

    .wrapper {
      max-width: 580px;
      margin: 0 auto;
    }

    /* ── HEADER ──────────────────────────────── */
    .header {
      background: #1e1a14;
      border-radius: 16px 16px 0 0;
      padding: 28px 36px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .brand-wrap { display: flex; flex-direction: column; }
    .brand      { font-size: 24px; font-weight: 700; color: #c17f3e; letter-spacing: 1px; }
    .brand-sub  { font-size: 11px; color: #7a6e65; letter-spacing: 2px; text-transform: uppercase; margin-top: 3px; }
    .header-badge {
      background: #c17f3e;
      color: #fff;
      font-size: 11px;
      font-weight: 600;
      padding: 6px 14px;
      border-radius: 20px;
      letter-spacing: .5px;
      white-space: nowrap;
    }

    /* ── ALERT BANNER ────────────────────────── */
    .alert-banner {
      background: linear-gradient(135deg, #fdf5ec, #faebd7);
      border-left: 4px solid #c17f3e;
      padding: 18px 24px;
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .alert-icon  { font-size: 2.2rem; flex-shrink: 0; }
    .alert-text  { flex: 1; }
    .alert-title { font-size: 17px; font-weight: 700; color: #1a1a1a; margin-bottom: 3px; }
    .alert-desc  { font-size: 13px; color: #6b5b47; line-height: 1.5; }
    .alert-desc strong { color: #c17f3e; }
    .alert-time  { font-size: 11px; color: #a09080; margin-top: 4px; }

    /* ── BODY ────────────────────────────────── */
    .body {
      background: #ffffff;
      padding: 28px 36px;
    }

    .greeting {
      font-size: 15px;
      color: #4a3f35;
      margin-bottom: 24px;
      line-height: 1.7;
    }
    .greeting strong { color: #1a1a1a; }

    /* ── DETAIL BOXES ────────────────────────── */
    .box {
      border: 1px solid #e8e2d9;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 16px;
    }
    .box-head {
      padding: 10px 16px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .box-head.tenant { background: #1e1a14; color: #c17f3e; }
    .box-head.booking { background: #fdf5ec; color: #92400e; border-bottom: 1px solid #f0d9bb; }
    .box-head.kamar   { background: #f0faf4; color: #065f46; border-bottom: 1px solid #a7f3d0; }

    .box-row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      padding: 10px 16px;
      border-bottom: 1px solid #f5f0eb;
      gap: 12px;
    }
    .box-row:last-child { border: none; }
    .row-label { font-size: 12.5px; color: #8a7968; white-space: nowrap; flex-shrink: 0; }
    .row-val   { font-size: 13px; font-weight: 500; color: #1a1a1a; text-align: right; }
    .row-val.harga   { color: #c17f3e; font-size: 15px; font-weight: 700; }
    .row-val.metode  { font-size: 12.5px; }
    .row-val.email-link a { color: #c17f3e; text-decoration: none; }

    /* Catatan tenant */
    .catatan-box {
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 10px;
      padding: 12px 16px;
      margin-bottom: 16px;
      font-size: 13px;
      color: #78350f;
      line-height: 1.6;
    }
    .catatan-label { font-weight: 600; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }

    /* ── ACTION BUTTONS ──────────────────────── */
    .action-wrap {
      margin: 24px 0 16px;
      text-align: center;
    }
    .action-title {
      font-size: 13px;
      font-weight: 600;
      color: #6b5b47;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: 14px;
    }
    .btn-group {
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .btn-primary {
      display: inline-block;
      background: #c17f3e;
      color: #ffffff !important;
      text-decoration: none;
      padding: 13px 28px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: .3px;
    }
    .btn-secondary {
      display: inline-block;
      background: #1e1a14;
      color: #faf8f4 !important;
      text-decoration: none;
      padding: 13px 28px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: .3px;
    }

    /* ── REMINDER BOX ────────────────────────── */
    .reminder {
      background: #f0faf4;
      border: 1px solid #a7f3d0;
      border-radius: 10px;
      padding: 14px 18px;
      margin-bottom: 16px;
    }
    .reminder-title {
      font-size: 12px;
      font-weight: 700;
      color: #065f46;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-bottom: 8px;
    }
    .reminder-item {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      font-size: 13px;
      color: #047857;
      margin-bottom: 6px;
      line-height: 1.5;
    }
    .reminder-item:last-child { margin: 0; }
    .r-num {
      min-width: 20px;
      height: 20px;
      border-radius: 50%;
      background: #059669;
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 1px;
    }

    /* ── WHATSAPP LINK ───────────────────────── */
    @if($telpTenant !== '-')
    .wa-box {
      background: #f0fdf4;
      border: 1px solid #86efac;
      border-radius: 10px;
      padding: 12px 16px;
      margin-bottom: 16px;
      text-align: center;
      font-size: 13px;
      color: #166534;
    }
    .wa-link {
      display: inline-block;
      margin-top: 8px;
      background: #22c55e;
      color: #fff !important;
      text-decoration: none;
      padding: 8px 20px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
    }
    @endif

    /* ── FOOTER ──────────────────────────────── */
    .footer {
      background: #1e1a14;
      border-radius: 0 0 16px 16px;
      padding: 20px 36px;
      text-align: center;
    }
    .footer p { font-size: 11.5px; color: #7a6e65; line-height: 1.9; }
    .footer a  { color: #c17f3e; text-decoration: none; }
  </style>
</head>
<body>
<div class="wrapper">

  <!-- HEADER -->
  <div class="header">
    <div class="brand-wrap">
      <div class="brand">KosDili</div>
      <div class="brand-sub">Dili · Timor-Leste</div>
    </div>
    <div class="header-badge">🔔 Booking Baru</div>
  </div>

  <!-- ALERT BANNER -->
  <div class="alert-banner">
    <div class="alert-icon">📬</div>
    <div class="alert-text">
      <div class="alert-title">Ada booking baru untuk {{ $namaKos }}!</div>
      <div class="alert-desc">
        <strong>{{ $namaTenant }}</strong> ingin menyewa kamar Anda.
        Segera tinjau dan berikan konfirmasi.
      </div>
      <div class="alert-time">🕐 Diterima: {{ $waktuBooking }}</div>
    </div>
  </div>

  <!-- BODY -->
  <div class="body">

    <p class="greeting">
      Halo, <strong>{{ $namaOwner }}</strong>! 👋<br><br>
      Anda mendapat permintaan booking baru di <strong>{{ $namaKos }}</strong>.
      Berikut detail lengkapnya — harap segera dikonfirmasi agar calon penghuni
      tidak menunggu terlalu lama.
    </p>

    <!-- Data tenant -->
    <div class="box">
      <div class="box-head tenant">👤 Data Calon Penghuni</div>
      <div class="box-row">
        <span class="row-label">Nama</span>
        <span class="row-val">{{ $namaTenant }}</span>
      </div>
      <div class="box-row">
        <span class="row-label">Email</span>
        <span class="row-val email-link">
          <a href="mailto:{{ $emailTenant }}">{{ $emailTenant }}</a>
        </span>
      </div>
      @if($telpTenant !== '-')
      <div class="box-row">
        <span class="row-label">No. HP / WA</span>
        <span class="row-val">{{ $telpTenant }}</span>
      </div>
      @endif
    </div>

    <!-- Data kamar & kos -->
    <div class="box">
      <div class="box-head kamar">🏠 Detail Kamar</div>
      <div class="box-row">
        <span class="row-label">Nama Kos</span>
        <span class="row-val">{{ $namaKos }}</span>
      </div>
      <div class="box-row">
        <span class="row-label">Alamat</span>
        <span class="row-val">{{ $alamatKos }}</span>
      </div>
      <div class="box-row">
        <span class="row-label">Nomor Kamar</span>
        <span class="row-val">Kamar {{ $nomorKamar }}</span>
      </div>
      <div class="box-row">
        <span class="row-label">Harga / Bulan</span>
        <span class="row-val">{{ $hargaKamar }}</span>
      </div>
    </div>

    <!-- Data booking -->
    <div class="box">
      <div class="box-head booking">📋 Detail Booking</div>
      <div class="box-row">
        <span class="row-label">Tanggal Masuk</span>
        <span class="row-val">{{ $tanggalMasuk }}</span>
      </div>
      <div class="box-row">
        <span class="row-label">Tanggal Keluar</span>
        <span class="row-val">{{ $tanggalKeluar }}</span>
      </div>
      <div class="box-row">
        <span class="row-label">Durasi Sewa</span>
        <span class="row-val">{{ $durasiBooking }} bulan</span>
      </div>
      <div class="box-row">
        <span class="row-label">Total Harga</span>
        <span class="row-val harga">{{ $totalHarga }}</span>
      </div>
      <div class="box-row">
        <span class="row-label">Metode Bayar</span>
        <span class="row-val metode">{{ $metodeBayar }}</span>
      </div>
    </div>

    <!-- Catatan dari tenant -->
    @if($catatanTenant)
    <div class="catatan-box">
      <div class="catatan-label">📝 Catatan dari Calon Penghuni</div>
      "{{ $catatanTenant }}"
    </div>
    @endif

    <!-- Hubungi via WhatsApp -->
    @if($telpTenant !== '-')
    <div class="wa-box">
      Ingin konfirmasi langsung ke calon penghuni?
      <br>
      <a
        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $telpTenant) }}?text={{ urlencode('Halo ' . $namaTenant . ', saya pemilik ' . $namaKos . '. Booking kamar ' . $nomorKamar . ' Anda sedang saya tinjau.') }}"
        class="wa-link"
      >
        💬 Chat via WhatsApp
      </a>
    </div>
    @endif

    <!-- Tombol aksi -->
    <div class="action-wrap">
      <div class="action-title">⚡ Tindakan Diperlukan</div>
      <div class="btn-group">
        <a href="{{ $linkDashboard }}" class="btn-primary">
          ✅ Buka Dashboard & Konfirmasi
        </a>
      </div>
    </div>

    <!-- Reminder langkah -->
    <div class="reminder">
      <div class="reminder-title">📌 Yang perlu Anda lakukan</div>
      <div class="reminder-item">
        <div class="r-num">1</div>
        <span>Buka dashboard KosDili lewat tombol di atas.</span>
      </div>
      <div class="reminder-item">
        <div class="r-num">2</div>
        <span>Periksa data calon penghuni dan pastikan kamar tersedia.</span>
      </div>
      <div class="reminder-item">
        <div class="r-num">3</div>
        <span>Klik <strong>Setuju</strong> atau <strong>Tolak</strong> — calon penghuni akan otomatis menerima email notifikasi.</span>
      </div>
      @if($metodeBayar === '💵 Cash langsung')
      <div class="reminder-item">
        <div class="r-num">4</div>
        <span>Jika disetujui, terima pembayaran cash dari penghuni lalu konfirmasi di aplikasi.</span>
      </div>
      @else
      <div class="reminder-item">
        <div class="r-num">4</div>
        <span>Jika disetujui, penghuni akan upload bukti transfer. Verifikasi sebelum kamar diserahkan.</span>
      </div>
      @endif
    </div>

  </div>

  <!-- FOOTER -->
  <div class="footer">
    <p>
      Email ini dikirim otomatis oleh <strong style="color:#c17f3e;">KosDili</strong>
      karena ada booking baru di properti Anda.<br>
      Jangan balas email ini langsung.<br>
      Untuk pertanyaan: <a href="mailto:support@kosdili.com">support@kosdili.com</a><br><br>
      © {{ date('Y') }} KosDili · Dili, Timor-Leste
    </p>
  </div>

</div>
</body>
</html>