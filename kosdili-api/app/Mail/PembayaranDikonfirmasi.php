<?php
// app/Mail/PembayaranDikonfirmasi.php
// Jalankan: php artisan make:mail PembayaranDikonfirmasi

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PembayaranDikonfirmasi extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💵 Pembayaran Dikonfirmasi — KosDili'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pembayaran-dikonfirmasi',
            with: [
                'namaUser'    => $this->booking->user->name ?? 'Pengguna',
                'namaKos'     => $this->booking->kamar->kos->nama_kos ?? '-',
                'nomorKamar'  => $this->booking->kamar->nomor_kamar ?? '-',
                'tanggalMasuk'=> \Carbon\Carbon::parse($this->booking->tanggal_masuk)->format('d M Y'),
                'tanggalKeluar'=> \Carbon\Carbon::parse($this->booking->tanggal_keluar)->format('d M Y'),
                'totalHarga'  => '$' . number_format($this->booking->kamar->harga * $this->booking->durasi_bulan, 0),
            ]
        );
    }
}