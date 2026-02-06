<x-guest-layout>
<div class="max-w-md mx-auto mt-10 bg-white p-6 rounded shadow">
    <h2 class="text-lg font-bold mb-4">パスワードをお忘れですか？</h2>

    @if (session('status'))
        <div class="mb-4 text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('user.password.email') }}">
        @csrf

        <div class="mb-4">
            <label class="block mb-1">メールアドレス</label>
            <input
                type="email"
                name="email"
                class="w-full border rounded px-3 py-2"
                required
            >
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button class="w-full bg-user text-white py-2 rounded">
            再設定メールを送信
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('user.login') }}" class="text-sm text-gray-600 underline">
            ログイン画面へ戻る
        </a>
    </div>
</div>
</x-guest-layout>

