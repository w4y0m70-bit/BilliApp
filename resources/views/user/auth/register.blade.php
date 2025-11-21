<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">

            <h2 class="text-2xl font-bold mb-6 text-center">新規登録</h2>

            @if ($errors->any())
                <div class="mb-4 bg-red-100 text-red-700 p-3 rounded">
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>・{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('user.register.post') }}" method="POST">
        @csrf

        {{-- 基本情報 --}}
        <div class="mb-3">
            <label class="block mb-1">氏名</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-3">
            <label class="block mb-1">性別</label>
            <select name="gender" class="w-full border rounded px-3 py-2">
                <option value="">選択してください</option>
                <option value="male" {{ old('gender')=='male'?'selected':'' }}>男性</option>
                <option value="female" {{ old('gender')=='female'?'selected':'' }}>女性</option>
                <option value="other" {{ old('gender')=='other'?'selected':'' }}>その他</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="block mb-1">誕生日</label>
            <input type="date" name="birthday" value="{{ old('birthday') }}" class="w-full border rounded px-3 py-2">
        </div>

        {{-- 連絡先・プロフィール --}}
        <div class="mb-3">
            <label class="block mb-1">住所</label>
            <input type="text" name="address" value="{{ old('address') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-3">
            <label class="block mb-1">電話番号</label>
            <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-3">
    <label class="block font-mb-1">アカウント名（任意。後で変更できます）</label>
    <input type="text" name="account_name"
        value="{{ old('account_name', $user->account_name ?? '') }}"
        class="w-full border p-2 rounded">
</div>

        <div class="mb-3">
    <label class="block font-mb-1">クラス</label>
    <select name="class" class="w-full border p-2 rounded" required>
        <option value="">選択してください</option>
        @foreach(['Beginner','C','B','A','Pro'] as $c)
            <option value="{{ $c }}" @selected(old('class') === $c)>
                {{ $c }}
            </option>
        @endforeach
    </select>
</div>

        <div class="mb-3">
    <label class="block mb-1">通知先（キャンセル待ち～エントリーなどの通知）</label>
    <select name="notification_type" class="w-full border p-2 rounded" required>
        <option value="email">メール</option>
        <option value="sms">SMS</option>
        <option value="line">LINE</option>
    </select>
</div>

        {{-- ログイン情報 --}}
        <div class="mb-3">
            <label class="block mb-1">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-3">
            <label class="block mb-1">パスワード（8文字以上）</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-3">
            <label class="block mb-1">パスワード確認</label>
            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required>
        </div>

        <button type="submit" class="bg-user text-white px-4 py-2 rounded hover:bg-user-dark">
            登録
        </button>
        <a href="{{ route('user.login') }}" class="ml-4 text-gray-600 hover:underline">ログインに戻る</a>
    </form>
</div>
</x-guest-layout>
