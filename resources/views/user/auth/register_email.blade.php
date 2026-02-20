<x-guest-layout>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 px-4">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">新規登録</h2>
            
            <p class="text-sm text-gray-600 mb-6 text-center leading-relaxed">
                ご入力いただいたメールアドレスに、<br>登録用URLを送信します。
            </p>

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('user.register.email.post') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" 
                           required placeholder="example@mail.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-user text-white font-bold py-3 rounded-lg hover:bg-opacity-90 transition shadow-sm">
                    登録用URLを送信する
                </button>
                {{-- キャリアメールへの注釈 --}}
                <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <p class="text-[10px] text-gray-500 leading-tight">
                        ※キャリアメール（docomo, au, softbank）をご利用の場合、設定によりメールが届かないことがあります。
                        その場合は「billents.com」を受信許可してください。
                        迷惑メールに入っていないかもご確認ください。もしくは下のボタンでLINE登録をご利用ください。
                    </p>
                </div>
            </form>

            {{-- セパレーター --}}
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t border-gray-200"></span>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-white px-2 text-gray-400 font-medium">または</span>
                </div>
            </div>

            {{-- LINE連携ボタン --}}
            <div class="text-center">
                
                <a href="{{ route('user.line.login') }}" class="flex items-center justify-center w-full bg-[#06C755] hover:bg-[#05b34c] text-white font-bold py-3 rounded-lg shadow-sm transition">
                    <img src="{{ asset('images/LINE_Brand_icon.png') }}" 
                            alt="LINEアイコン" 
                            class="w-12 h-12 sm:w-6 sm:h-6 object-contain">
                    LINEで登録
                </a>
            </div>
        </div>

        <div class="mt-6 text-center space-x-2">
            <a href="{{ route('user.login') }}" class="text-sm text-gray-500 hover:text-gray-800 hover:underline transition">
                ログイン画面へ戻る
            </a>
            <span class="text-gray-300">|</span>
            <a href="/" class="text-sm text-gray-500 hover:text-gray-800 hover:underline transition">
                サイトトップへ
            </a>
        </div>
    </div>
</x-guest-layout>