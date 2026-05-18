<?php

namespace App\Mail;

use App\Models\Kos;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KosStatusMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Kos $kos,
        public string $status
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Status Kos Anda — KosDili');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.kos-status');
    }
}