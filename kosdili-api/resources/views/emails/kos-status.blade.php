<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; padding: 2rem; background: #faf8f4;">
  <div style="max-width:500px; margin:0 auto; background:#fff; border-radius:12px; padding:2rem; border:1px solid #e8e2d9;">
    <h2 style="font-family:serif; color:#1a1a1a;">KosDili</h2>
    <p>Halo <strong>{{ $kos->user->name }}</strong>,</p>

    @if($status === 'aktif')
      <p>🎉 Kos Anda <strong>{{ $kos->nama_kos }}</strong> telah <span style="color:#065f46; font-weight:bold;">DIAKTIFKAN</span> oleh admin dan sekarang sudah tampil di halaman publik.</p>
    @elseif($status === 'nonaktif')
      <p>⚠️ Kos Anda <strong>{{ $kos->nama_kos }}</strong> telah <span style="color:#991b1b; font-weight:bold;">DINONAKTIFKAN</span> oleh admin. Silakan hubungi admin untuk informasi lebih lanjut.</p>
    @endif

    <hr style="border:none; border-top:1px solid #e8e2d9; margin:1.5rem 0;">
    <p style="font-size:12px; color:#8a7968;">KosDili · Dili, Timor-Leste</p>
  </div>
</body>
</html>