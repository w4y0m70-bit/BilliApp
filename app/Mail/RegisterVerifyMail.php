<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RegisterVerifyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $url; // 署名付きURLを受け取る変数

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function build()
    {
        return $this->subject('【Billents】新規ユーザー登録のお手続き')
                    ->view('emails.register_verify'); // メールの本文（Blade）を指定
    }
}