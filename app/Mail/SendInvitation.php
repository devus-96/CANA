<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SendInvitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $user, public string $email)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        App::setLocale($this->user->lang ?? config('app.locale'));
        return new Envelope(
            subject: __('invitation-account.subject', ['app' => config('app.name')]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        App::setLocale($this->user->lang ?? config('app.locale'));
        return new Content(
            markdown: 'mail.invitation-account',
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
