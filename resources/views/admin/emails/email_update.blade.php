<p>管理者様</p>

<p>メールアドレス変更のリクエストを受け付けました。</p>
<p>以下のボタンをクリックして、新しいメールアドレスの認証を完了させてください。</p>

<a href="{{ route('admin.account.email.verify', ['token' => $token]) }}" 
   style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
    メールアドレスを確定する
</a>

<p>※このリンクの有効期限は24時間です。</p>
<p>もし心当たりがない場合は、このメールを破棄してください。</p>