<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">🔑 管理者ログイン</h2>

            {{-- エラーメッセージ --}}
            @if ($errors->any())
                <div class="mb-4 text-red-600 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1 font-medium">管理者ID</label>
                    <input type="text" name="admin_id" class="border w-full p-2 rounded" placeholder="admin001">
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
                <a href="{{ route('admin.password.request') }}" class="text-sm text-blue-600 hover:underline">
                    パスワードをお忘れの方はこちら</a>
                    <x-help help-key="admin.login.password_reset" />
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('top') }}" class="text-gray-500 hover:underline">← トップページへ戻る</a>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('admin.register') }}" class="text-blue-600 hover:underline">
                    新規登録
                </a>
                <x-help help-key="admin.login.register" />
            </div>
        </div>
    </div>
</x-guest-layout>
