<x-guest-layout>
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

    <div class="min-h-screen bg-gray-100 py-10 px-4">
        <div class="bg-white p-6 md:p-8 rounded-xl shadow-md w-full max-w-2xl mx-auto my-10">
            <h2 class="text-2xl font-bold mb-2 text-center text-gray-800">新規登録</h2>

            {{-- 警告・エラー表示 (編集画面と共通のパーツ) --}}
            @include('user.account._alerts')

            <form action="{{ route('user.register.post') }}" method="POST" class="h-adr space-y-5">
                @csrf
                
                {{-- 
                    共通フィールド（氏名、フリガナ、性別、生年月日、住所、電話番号、アカウント名、クラス）
                    ※$userがいない新規登録時でも動作するよう設計済み
                --}}
                @include('user.account._fields')

                <hr class="border-gray-100 my-6">

                {{-- ログイン情報 --}}
                <div class="space-y-4">
                    <div class="bg-gray-50 p-3 rounded border border-gray-200">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">ログイン用メールアドレス</label>
                        
                        {{-- text-lg から text-base にし、break-all で枠内での改行を許可 --}}
                        <p class="font-bold text-gray-800 text-base break-all">{{ $email }}</p>
                        
                        <input type="hidden" name="email" value="{{ $email }}">
                        <p class="text-[10px] text-green-600 mt-1 flex items-center font-medium">
                            <span class="material-symbols-outlined text-[14px] mr-1">verified</span>認証済み
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input name="password" type="password" label="パスワード" placeholder="8文字以上" required class="mb-0" />
                        <x-form.input name="password_confirmation" type="password" label="確認用" placeholder="もう一度入力" required class="mb-0" />
                    </div>
                </div>

                {{-- 通知設定（新規登録時はシンプルに） --}}
                <div class="p-4 border rounded-lg bg-blue-50/30 border-blue-100">
                    <label class="block text-sm font-bold text-gray-700 mb-2">通知設定</label>
                    <x-form.checkbox 
                        name="notification_via[]" 
                        value="mail" 
                        label="メール通知を受け取る（推奨）" 
                        :checked="!old('notification_via') || (is_array(old('notification_via')) && in_array('mail', old('notification_via')))"
                        reason="※エントリー状況などの大切な案内が届きます"
                    />
                </div>

                <div class="pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <button type="submit" class="w-full md:w-auto bg-user text-white px-10 py-3 rounded-full font-bold hover:opacity-90 shadow-md transition text-center">
                        登録
                    </button>
                    <!-- <a href="{{ route('user.login') }}" class="text-sm text-gray-500 hover:underline">
                        ログインに戻る
                    </a> -->
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>