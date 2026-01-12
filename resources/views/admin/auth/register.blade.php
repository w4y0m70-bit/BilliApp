<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">新規登録</h2>

            @if ($errors->any())
                <div class="mb-4 text-red-600 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.register.post') }}" method="POST">
        @csrf

        {{-- 基本情報 --}}
        <div class="mb-3">
            <label class="block mb-1">ログインID（任意、空欄の場合自動生成）</label>
            <input type="text" name="admin_id" value="{{ old('admin_id') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-3">
            <label class="block mb-1">店舗名</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-3">
            <label class="block mb-1">担当者名（任意）</label>
            <input type="text" name="manager_name" value="{{ old('manager_name') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-3">
            <label class="block mb-1">電話番号</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-3">
            <label class="block mb-1">住所</label>
            <input type="text" name="address" value="{{ old('address') }}" class="w-full border rounded px-3 py-2">
        </div>

        {{-- ログイン情報 --}}
        <div class="mb-4 bg-gray-50 p-3 rounded border">
            <label class="block text-sm font-medium text-gray-700">メールアドレス</label>
            <input type="email" name="email" value="{{ $email }}" readonly 
                class="mt-1 block w-full border-none bg-transparent font-bold text-gray-900 focus:ring-0">
            <p class="text-xs text-gray-500 mt-1">※このアドレスは認証済みです</p>
        </div>

        <div class="mb-3">
            <label class="block mb-1">パスワード（8文字以上）</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-3">
            <label class="block mb-1">パスワード確認</label>
            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-3">
            <label class="block mb-1">通知設定</label>
            <div class="flex gap-4 p-2 border rounded bg-white">
                {{-- メール: 初期状態でチェックを入れる --}}
                <label class="inline-flex items-center">
                    <input type="checkbox" name="notification_via[]" value="mail" 
                        {{-- oldが空（初回表示）の時、またはoldに'mail'が含まれる時にチェック --}}
                        @checked(!old('notification_via') || (is_array(old('notification_via')) && in_array('mail', old('notification_via'))))>
                    <span class="ml-2">メール</span>
                </label>
                {{-- LINE: oldに'line'が含まれる時だけチェック --}}
                <!-- <label class="inline-flex items-center">
                    <input type="checkbox" name="notification_via[]" value="line" 
                        @checked(is_array(old('notification_via')) && in_array('line', old('notification_via')))>
                    <span class="ml-2">LINE</span>
                </label> -->
            </div>
            <span class="text-sm text-gray-500">※メールでの通知を推奨</span>
        </div>

        <button type="submit" class="bg-admin text-white px-4 py-2 rounded hover:bg-admin-dark">
            登録
        </button>
        <a href="{{ route('admin.login') }}" class="ml-4 text-gray-600 hover:underline">ログインに戻る</a>
    </form>
</div>
</x-guest-layout>
