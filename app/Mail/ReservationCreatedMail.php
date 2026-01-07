<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle réservation AfricaLocation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation_created', // ta vue Blade
            with: [
                'reservation' => $this->reservation,
                'owner_name' => $this->reservation->bien->owner->name ?? 'Propriétaire',
                'bienTitle' => $this->reservation->bien->title ?? '—',
                'clientName' => $this->reservation->client_name,
                'clientEmail' => $this->reservation->client_email,
                'clientPhone' => $this->reservation->client_phone,
                'clientMessage' => $this->reservation->message ?? '—',
                'startDate' => $this->reservation->start_date?->format('d/m/Y') ?? '—',
                'endDate' => $this->reservation->end_date?->format('d/m/Y') ?? '—',
                'visitDate' => $this->reservation->visit_date?->format('d/m/Y') ?? '—',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
