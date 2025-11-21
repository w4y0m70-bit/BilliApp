<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">🔑 管理者ログイン</h2>

            <form action="{{ route('admin.login.post') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1 font-medium">メールアドレス</label>
                    <input type="email" name="email" class="border w-full p-2 rounded" placeholder="admin@example.com">
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">パスワード</label>
                    <input type="password" name="password" class="border w-full p-2 rounded" placeholder="********">
                </div>

                <button type="submit" class="w-full bg-admin text-white py-2 rounded hover:bg-admin-dark">
                    ログイン
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="#" class="text-sm text-blue-600 hover:underline">パスワードをお忘れの方はこちら</a>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('top') }}" class="text-gray-500 hover:underline">← トップページへ戻る</a>
            </div>

            <div class="mt-4 text-center">
    <a href="{{ route('admin.register') }}" class="text-blue-600 hover:underline">
        新規登録
    </a>
</div>
        </div>
    </div>
</x-guest-layout>
