<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">プレイヤーログイン</h2>

            {{-- エラーメッセージの表示エリア --}}
            @if ($errors->any())
                <div class="mb-4 font-medium text-sm text-red-600">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ログインフォーム --}}
            <form method="POST" action="{{ route('user.login.post') }}">
                @csrf

                <div class="mb-4">
                    <label class="block mb-1 font-medium">メールアドレス</label>
                    <input type="text" name="email" class="border w-full p-2 rounded value="{{ old('email') }}" placeholder="mail@xxx.com">
                </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">パスワード</label>
                    <input type="password" name="password" class="border w-full p-2 rounded" placeholder="********">
                </div>

                <button type="submit" class="w-full bg-user text-white py-2 rounded hover:bg-user-dark transition">
                    ログイン
                </button>
            </form>

            {{-- LINEログインへの導線 --}}
            <div class="mt-6 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">または</span>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('user.line.login') }}" class="w-full inline-flex justify-center items-center bg-[#06C755] hover:bg-[#05b34c] text-white font-bold py-2 px-4 rounded transition shadow-sm">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M24 10.304c0-4.577-4.705-8.304-10.5-8.304s-10.5 3.727-10.5 8.304c0 4.1 3.73 7.532 8.763 8.203.34.073.805.225.922.518.105.266.07.682.034 0.951-.137.956-.504 3.824-.576 4.373-.086.643.398.35.586.233.189-.118 3.16-2.147 4.453-3.13 1.293-.983 2.107-1.503 2.107-1.503s.013 0 .013 0c3.486-.613 6.648-3.414 6.648-6.648z"/>
                    </svg>
                    LINEでログイン
                </a>
                <span class="text-gray-500 text-xs mt-1 inline-block w-full text-center">アカウントページで連携が必要です</span>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('user.password.request') }}" class="text-sm text-user hover:underline">パスワードをお忘れの方はこちら</a>
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
