<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">新規登録</h2>
            <p class="text-sm text-gray-600 mb-4">
                ご入力いただいたメールアドレスに、登録用URLを送信します。
            </p>

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-100 p-3 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('admin.register.email.post') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block mb-1">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="w-full border rounded px-3 py-2" required placeholder="example@mail.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-admin text-white font-bold py-2 rounded hover:bg-blue-700 transition">
                    登録用URLを送信する
                </button>
            </form>
        </div>
        <div class="mt-4 text-center">
            <a href="{{ route('admin.login') }}" class="text-sm text-gray-600 hover:underline">
                ログイン画面へ戻る
            </a>

            <span class="mx-2 text-gray-300">|</span>

            <a href="/" class="text-sm text-gray-600 hover:underline">
                サイトトップへ
            </a>
        </div>
    </div>
</x-guest-layout>