<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminEmailUpdateVerification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '【重要】メールアドレス変更の確認');
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin.emails.email_update',
            with: [
                'url' => route('admin.account.email.verify', ['token' => $this->token]),
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
