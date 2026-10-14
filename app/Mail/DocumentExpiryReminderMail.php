<?php

namespace App\Mail;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Document $document,
        public User $recipient,
        public int $offsetDays
    ) {}

    public function envelope(): Envelope
    {
        $subject = match (true) {
            $this->offsetDays > 1 => "Reminder: {$this->document->title} expires in {$this->offsetDays} days",
            $this->offsetDays === 1 => "Urgent: {$this->document->title} expires tomorrow",
            $this->offsetDays === 0 => "Action Required: {$this->document->title} expires today",
            default => "Alert: {$this->document->title} is overdue",
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document_expiry_reminder',
        );
    }

    public function attachments(): array
    {
        return []; // Strict privacy: do not attach sensitive files to email
    }
}
