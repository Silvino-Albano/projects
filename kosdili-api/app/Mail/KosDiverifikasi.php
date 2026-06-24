<?php
// app/Mail/KosDiverifikasi.php
// Jalankan: php artisan make:mail KosDiverifikasi

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KosDiverifikasi extends Mailable
{
    use Queueable, SerializesModels;

    public $kos;
    public string $status;
    public ?string $catatan;

    public function __construct($kos, string $status, ?string $catatan = null)
    {
        $this->kos     = $kos;
        $this->status  = $status;
        $this->catatan = $catatan;
    }

    public function envelope(): Envelope
    {
        $subjek = $this->status === 'aktif'
            ? "✅ Kos Anda Disetujui — KosDili"
            : "❌ Kos Anda Ditolak — KosDili";

        return new Envelope(subject: $subjek);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.kos-diverifikasi',
            with: [
                'namaOwner' => $this->kos->user->name ?? 'Pemilik',
                'namaKos'   => $this->kos->nama_kos,
                'alamat'    => $this->kos->alamat,
                'status'    => $this->status,
                'catatan'   => $this->catatan,
                'linkDash'  => config('app.url') . '/owner/dashboard',
            ]
        );
    }
}