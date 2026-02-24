<div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mt-6">
    <label class="block text-sm font-bold mb-3 text-blue-800 flex items-center">
        <span class="material-symbols-outlined text-sm mr-1">lock</span>
        ログインパスワード（メールアドレスでログインする場合に必要です）
    </label>
    
    <!-- @if(empty($user->password))
        <div class="flex items-center p-2 mb-2 text-amber-800 bg-amber-50 rounded-lg border border-amber-200">
            <span class="material-symbols-outlined mr-2 text-sm">priority_high</span>
            <p class="text-xs">現在パスワードが設定されていません。メールアドレスでログインするには設定が必要です。</p>
        </div>
    @else
        <div class="flex items-center p-2 mb-2 text-green-800 bg-green-50 rounded-lg border border-green-200">
            <span class="material-symbols-outlined mr-2 text-sm">check_circle</span>
            <p class="text-xs">パスワードは設定済みです。変更したい場合は入力してください。</p>
        </div>
    @endif -->

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="password" type="password" label="新しいパスワード" placeholder="8文字以上" />
        <x-form.input name="password_confirmation" type="password" label="パスワード(確認)" placeholder="もう一度入力" />
    </div>
</div>