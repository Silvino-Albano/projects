<?php
// app/Mail/BookingDikonfirmasi.php
// Jalankan dulu: php artisan make:mail BookingDikonfirmasi

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingDikonfirmasi extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string  $statusLabel;  // 'disetujui' atau 'ditolak'

    public function __construct(Booking $booking, string $status)
    {
        $this->booking     = $booking;
        $this->statusLabel = $status;
    }

    public function envelope(): Envelope
    {
        $subjek = $this->statusLabel === 'confirmed'
            ? '✅ Booking Anda Disetujui — KosDili'
            : '❌ Booking Anda Ditolak — KosDili';

        return new Envelope(subject: $subjek);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-dikonfirmasi',
            with: [
                'booking'     => $this->booking,
                'status'      => $this->statusLabel,
                'namaUser'    => $this->booking->user->name ?? 'Pengguna',
                'namaKos'     => $this->booking->kamar->kos->nama_kos ?? '-',
                'alamatKos'   => $this->booking->kamar->kos->alamat ?? '-',
                'nomorKamar'  => $this->booking->kamar->nomor_kamar ?? '-',
                'tanggalMasuk'=> \Carbon\Carbon::parse($this->booking->tanggal_masuk)->format('d M Y'),
                'tanggalKeluar'=> \Carbon\Carbon::parse($this->booking->tanggal_keluar)->format('d M Y'),
                'totalHarga'  => '$' . number_format($this->booking->kamar->harga * $this->booking->durasi_bulan, 0),
                'metodeBayar' => $this->booking->payment_method === 'cash'
                                    ? 'Cash langsung ke pemilik'
                                    : 'Transfer bank',
                'namaPemilik' => $this->booking->kamar->kos->user->name ?? '-',
                'telpPemilik' => $this->booking->kamar->kos->user->phone ?? '-',
            ]
        );
    }
}