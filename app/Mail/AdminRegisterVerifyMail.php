<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminRegisterVerifyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $url; // 署名付きURLを受け取る変数

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function build()
    {
        return $this->subject('【店舗管理者】新規登録のお手続き')
                    ->view('emails.register_verify');
    }
}
