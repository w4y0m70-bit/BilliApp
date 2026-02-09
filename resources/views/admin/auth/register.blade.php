<x-guest-layout>
    {{-- yubinbango.js の読み込み --}}
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 py-10">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">管理者新規登録</h2>

            @if ($errors->any())
                <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>・{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- フォームに h-adr クラスを追加 --}}
            <form action="{{ route('admin.register.post') }}" method="POST" class="h-adr">
                @csrf
                {{-- 国名の指定（yubinbango用） --}}
                <span class="p-country-name" style="display:none;">Japan</span>

                {{-- 基本情報 --}}
                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium">ログインID（任意、空欄の場合自動生成）</label>
                    <input type="text" name="admin_id" value="{{ old('admin_id') }}" class="w-full border rounded px-3 py-2">
                </div>
                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium">店舗名 / 主催団体名</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium">担当者名（任意）</label>
                    <input type="text" name="manager_name" value="{{ old('manager_name') }}" class="w-full border rounded px-3 py-2">
                </div>

                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium">電話番号</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border rounded px-3 py-2">
                </div>

                {{-- 住所情報（自動入力対応） --}}
                <div class="mb-3 border-l-4 border-admin pl-3 py-1 bg-gray-50">
                    <label class="block mb-1 text-sm font-bold text-admin">所在地（郵便番号から自動入力）</label>
                    
                    <div class="mb-2">
                        <label class="text-xs text-gray-500">郵便番号 (ハイフンなし)</label>
                        <input type="text" name="zip_code" value="{{ old('zip_code') }}" 
                            class="p-postal-code w-full border rounded px-3 py-2" placeholder="1234567">
                    </div>

                    <div class="mb-2">
                        <label class="text-xs text-gray-500">都道府県</label>
                        <input type="text" name="prefecture" value="{{ old('prefecture') }}" 
                            class="p-region w-full border rounded px-3 py-2 bg-white" readonly>
                    </div>

                    <div class="mb-2">
                        <label class="text-xs text-gray-500">市区町村</label>
                        <input type="text" name="city" value="{{ old('city') }}" 
                            class="p-locality w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500">番地・建物名</label>
                        <input type="text" name="address_line" value="{{ old('address_line') }}" 
                            class="p-street-address p-extended-address w-full border rounded px-3 py-2">
                    </div>
                </div>

                {{-- ログイン情報 --}}
                <div class="mb-4 bg-gray-50 p-3 rounded border">
                    <label class="block text-sm font-medium text-gray-700">メールアドレス</label>
                    <input type="email" name="email" value="{{ $email }}" readonly 
                        class="mt-1 block w-full border-none bg-transparent font-bold text-gray-900 focus:ring-0">
                    <p class="text-xs text-gray-500 mt-1">※このアドレスは認証済みです</p>
                </div>

                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium">パスワード（8文字以上）</label>
                    <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="mb-6">
                    <label class="block mb-1 text-sm font-medium">パスワード確認</label>
                    <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="mb-6">
                    <label class="block mb-1 text-sm font-semibold">通知設定</label>
                    <div class="flex gap-4 p-3 border rounded bg-white">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notification_via[]" value="mail" 
                                @checked(!old('notification_via') || (is_array(old('notification_via')) && in_array('mail', old('notification_via'))))
                                class="rounded border-gray-300 text-admin shadow-sm focus:ring-admin">
                            <span class="ml-2 text-sm text-gray-700">メール</span>
                        </label>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block">※運営からの重要な連絡をメールで受け取ります。</span>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-admin text-white px-6 py-2 rounded font-bold hover:bg-admin-dark transition">
                        登録する
                    </button>
                    <a href="{{ route('admin.login') }}" class="text-sm text-gray-600 hover:underline">ログインに戻る</a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>