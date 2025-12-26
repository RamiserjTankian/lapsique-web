<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Event;
use App\Models\GuestListEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Customer $customer,
        public Event $event,
        public GuestListEntry $guestListEntry,
        public int $hoursBeforeEvent,
        public string $trackingToken
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔔 Recordatorio: {$this->event->title} en {$this->hoursBeforeEvent}h",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.event-reminder',
            with: [
                'customer' => $this->customer,
                'event' => $this->event,
                'guestListEntry' => $this->guestListEntry,
                'hoursBeforeEvent' => $this->hoursBeforeEvent,
                'trackingToken' => $this->trackingToken,
                'trackingPixelUrl' => route('email.track.open', ['token' => $this->trackingToken]),
                'eventUrl' => route('events.show', $this->event),
                'unsubscribeUrl' => route('customer.unsubscribe', ['email' => $this->customer->email]),
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
