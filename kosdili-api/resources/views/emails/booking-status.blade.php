<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; padding: 2rem; background: #faf8f4;">
  <div style="max-width:500px; margin:0 auto; background:#fff; border-radius:12px; padding:2rem; border:1px solid #e8e2d9;">
    <h2 style="font-family:serif; color:#1a1a1a;">KosDili</h2>
    <p>Halo <strong>{{ $booking->user->name }}</strong>,</p>

    @if($status === 'confirmed')
      <p>✅ Booking Anda untuk kos <strong>{{ $booking->kamar->kos->nama_kos }}</strong> — Kamar <strong>{{ $booking->kamar->nomor_kamar }}</strong> telah <span style="color:#065f46; font-weight:bold;">DIKONFIRMASI</span>.</p>
      <p><strong>Tanggal masuk:</strong> {{ $booking->tanggal_masuk }}</p>
      <p><strong>Tanggal keluar:</strong> {{ $booking->tanggal_keluar }}</p>
    @else
      <p>❌ Maaf, booking Anda untuk kos <strong>{{ $booking->kamar->kos->nama_kos }}</strong> telah <span style="color:#991b1b; font-weight:bold;">DIBATALKAN</span> oleh owner.</p>
    @endif

    <hr style="border:none; border-top:1px solid #e8e2d9; margin:1.5rem 0;">
    <p style="font-size:12px; color:#8a7968;">KosDili · Dili, Timor-Leste</p>
  </div>
</body>
</html>