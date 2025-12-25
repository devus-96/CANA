<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class AccountCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $user)
    {

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        App::setLocale($this->user->lang ?? config('app.locale'));
        return new Envelope(
            subject: __('account-created.subject', ['app' => config('app.name')]),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        App::setLocale($this->user->lang ?? config('app.locale'));
        return new Content(
            markdown: 'mail.account-created',
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
