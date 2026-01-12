<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">プレイヤーログイン</h2>

            {{-- 仮ログインフォーム --}}
            <form method="POST" action="{{ route('user.login.post') }}">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1 font-medium">メールアドレス</label>
                    <input type="text" name="email" class="border w-full p-2 rounded" placeholder="mail@xxx.com">
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">パスワード</label>
                    <input type="password" name="password" class="border w-full p-2 rounded" placeholder="********">
                </div>

                <button type="submit" class="w-full bg-user text-white py-2 rounded hover:bg-user-dark transition">
                    ログイン
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="#" class="text-sm text-user hover:underline">パスワードをお忘れの方はこちら</a>
                <x-help help-key="user.login.password_reset" />
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('top') }}" class="text-gray-500 hover:underline">← トップページへ戻る</a>
            </div>

            <div class="mt-4 text-center">
    <a href="{{ route('user.register.email') }}" class="text-blue-600 hover:underline">
        新規登録
    </a>
    <x-help help-key="user.login.register" />
</div>
        </div>
    </div>
</x-guest-layout>
