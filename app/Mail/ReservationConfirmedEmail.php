<?php

namespace App\Mail;

use App\Models\Erreserba;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Erreserba $erreserba,
        public string $reservasUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Erreserba baieztatuta - Nova Bites',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reserva-confirmada',
            with: [
                'reservasUrl' => $this->reservasUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
