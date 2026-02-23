<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserEmailUpdateVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $token; // トークンを格納するプロパティ

    /**
     * Create a new message instance.
     */
    public function __construct($token)
    {
        $this->token = $token; // コンストラクタで受け取る
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【重要】メールアドレス変更の確認',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'user.emails.user_email_update', // ユーザー用のビューを指定
        );
    }
}