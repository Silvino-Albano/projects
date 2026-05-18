<?php
// app/Mail/BookingBaruUntukOwner.php
// Jalankan: php artisan make:mail BookingBaruUntukOwner

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingBaruUntukOwner extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function envelope(): Envelope
    {
        $namaKos    = $this->booking->kamar->kos->nama_kos ?? 'Kos Anda';
        $namaTenant = $this->booking->user->name ?? 'Seseorang';

        return new Envelope(
            subject: "🔔 Booking Baru Masuk — {$namaKos}"
        );
    }

    public function content(): Content
    {
        $booking = $this->booking;
        $kamar   = $booking->kamar;
        $kos     = $kamar->kos;
        $tenant  = $booking->user;

        return new Content(
            view: 'emails.booking-baru-owner',
            with: [
                // Data pemilik
                'namaOwner'     => $kos->user->name ?? 'Pemilik',

                // Data kos & kamar
                'namaKos'       => $kos->nama_kos ?? '-',
                'alamatKos'     => $kos->alamat ?? '-',
                'nomorKamar'    => $kamar->nomor_kamar ?? '-',
                'hargaKamar'    => '$' . number_format($kamar->harga ?? 0, 0),

                // Data tenant (pencari kos)
                'namaTenant'    => $tenant->name ?? '-',
                'emailTenant'   => $tenant->email ?? '-',
                'telpTenant'    => $tenant->phone ?? $tenant->telepon ?? '-',

                // Data booking
                'tanggalMasuk'  => \Carbon\Carbon::parse($booking->tanggal_masuk)->format('d M Y'),
                'tanggalKeluar' => \Carbon\Carbon::parse($booking->tanggal_keluar)->format('d M Y'),
                'durasiBooking' => $booking->durasi_bulan ?? 1,
                'totalHarga'    => '$' . number_format(($kamar->harga ?? 0) * ($booking->durasi_bulan ?? 1), 0),
                'metodeBayar'   => $booking->payment_method === 'cash'
                                    ? '💵 Cash langsung'
                                    : '🏦 Transfer bank',
                'catatanTenant' => $booking->catatan ?? null,
                'waktuBooking'  => $booking->created_at->setTimezone('Asia/Dili')->format('d M Y, H:i') . ' WAST',

                // Link ke dashboard owner
                'linkDashboard' => config('app.url') . '/owner/bookings',
            ]
        );
    }
}