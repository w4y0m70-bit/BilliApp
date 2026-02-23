<p>こんにちは！</p>

<p>メールアドレスの変更リクエストを承りました。</p>
<p>お手続きを完了させるには、以下のボタンをクリックしてください。</p>

<div style="margin: 20px 0;">
    <a href="{{ route('user.email.verify', ['token' => $token]) }}" 
       style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
        新しいメールアドレスを認証する
    </a>
</div>

<p>※このリンクの有効期限は24時間です。</p>
<p>※もしこのメールに心当たりがない場合は、お手数ですがこのまま破棄してください。</p>

<hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
<p style="font-size: 0.8em; color: #777;">
    このメールはシステムより自動的に送信されています。
</p>