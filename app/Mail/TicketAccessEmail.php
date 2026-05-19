<?php

namespace App\Mail;

use App\Models\TicketAttendee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketAccessEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TicketAttendee $attendee,
        public string $trackingToken
    ) {
    }

    public function envelope(): Envelope
    {
        $eventTitle = $this->attendee->event?->title ?? 'Evento';

        return new Envelope(
            subject: "🎟️ Tu acceso para {$eventTitle}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-access',
            with: [
                'attendee' => $this->attendee,
                'event' => $this->attendee->event,
                'product' => $this->attendee->product,
                'order' => $this->attendee->order,
                'checkInUrl' => $this->attendee->getCheckInUrl(),
                'checkInQrUrl' => $this->attendee->getCheckInQrUrl(),
                'checkInCode' => $this->attendee->getCheckInCode(),
                'trackingToken' => $this->trackingToken,
                'trackingPixelUrl' => route('email.track.open', ['token' => $this->trackingToken]),
                'eventUrl' => $this->attendee->event
                    ? route('events.show', $this->attendee->event)
                    : route('events.index'),
                'recipientEmail' => $this->attendee->email,
                'unsubscribeUrl' => $this->attendee->email
                    ? route('customer.unsubscribe', ['email' => $this->attendee->email])
                    : route('customer.unsubscribe'),
            ],
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdfs.ticket-access', [
            'attendee' => $this->attendee,
            'event' => $this->attendee->event,
            'product' => $this->attendee->product,
            'order' => $this->attendee->order,
            'checkInUrl' => $this->attendee->getCheckInUrl(),
            'checkInQrUrl' => $this->attendee->getCheckInQrUrl(),
            'checkInCode' => $this->attendee->getCheckInCode(),
        ])->setOption('isRemoteEnabled', true);

        $filename = 'pase-' . $this->attendee->id . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
