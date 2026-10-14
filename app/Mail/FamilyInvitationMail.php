<?php

namespace App\Mail;

use App\Models\FamilyInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FamilyInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FamilyInvitation $invitation,
        public string $plainToken
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation to join {$this->invitation->family->name} on Family Document Expiry Tracker",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.family_invitation',
        );
    }
}
