<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingStatusMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $status
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Update Booking Anda — KosDili');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.booking-status');
    }
}