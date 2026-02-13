<x-guest-layout>
    {{-- 1. yubinbango.js の読み込み --}}
    <script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 py-10">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-lg">

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

            {{-- 2. フォームに h-adr クラスを追加 --}}
            <form action="{{ route('user.register.post') }}" method="POST" class="h-adr">
                @csrf
                {{-- 3. 国名の指定（yubinbango用） --}}
                <span class="p-country-name" style="display:none;">Japan</span>

                {{-- 基本情報 --}}
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">氏名</label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <input type="text" name="last_name" value="{{ old('last_name') }}" 
                                class="w-full border rounded px-3 py-2" placeholder="姓" required>
                        </div>
                        <div class="flex-1">
                            <input type="text" name="first_name" value="{{ old('first_name') }}" 
                                class="w-full border rounded px-3 py-2" placeholder="名" required>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">フリガナ（カタカナ）</label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <input type="text" name="last_name_kana" value="{{ old('last_name_kana') }}" 
                                class="w-full border rounded px-3 py-2" placeholder="セイ" required 
                                pattern="^[ァ-ヶー]+$">
                        </div>
                        <div class="flex-1">
                            <input type="text" name="first_name_kana" value="{{ old('first_name_kana') }}" 
                                class="w-full border rounded px-3 py-2" placeholder="メイ" required 
                                pattern="^[ァ-ヶー]+$">
                        </div>
                    </div>
                </div>

                {{-- 追加：性別と生年月日 --}}
                <div class="mb-4 flex gap-4">
                    <div class="flex-1">
                        <label class="block mb-1 text-sm font-medium">性別</label>
                        <div class="flex gap-3 mt-2">
                            @foreach(['男性' => '男性', '女性' => '女性', '未回答' => '回答しない'] as $value => $label)
                                <label class="inline-flex items-center">
                                    <input type="radio" name="gender" value="{{ $value }}" 
                                        @checked(old('gender') === $value) 
                                        class="rounded-full border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" required>
                                    <span class="ml-1 text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium">生年月日</label>
                    <input type="date" name="birthday" value="{{ old('birthday') }}" 
                        class="w-full border rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                {{-- 住所情報（ここを細分化） --}}
                <div class="mb-3 border-l-4 border-blue-500 pl-3 py-1 bg-blue-50">
                    <label class="block mb-1 text-sm font-bold">住所（郵便番号から自動入力）</label>
                    
                    <div class="mb-2">
                        <label class="text-xs text-gray-500">郵便番号 (ハイフンなし)</label>
                        <input type="text" name="zip_code" value="{{ old('zip_code') }}" 
                            class="p-postal-code w-full border rounded px-3 py-2" placeholder="1234567">
                    </div>

                    <div class="mb-2">
                        <label class="text-xs text-gray-500">都道府県</label>
                        <input type="text" name="prefecture" value="{{ old('prefecture') }}" 
                            class="p-region w-full border rounded px-3 py-2 bg-gray-50" readonly>
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

                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium">電話番号</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border rounded px-3 py-2">
                </div>

                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium">アカウント名（アプリ内で表示されます）</label>
                    <input type="text" name="account_name" value="{{ old('account_name') }}" class="w-full border p-2 rounded">
                </div>

                <div class="mb-3">
                    <label class="block mb-1 text-sm font-medium">クラス</label>
                    <select name="class" class="w-full border p-2 rounded" required>
                        <option value="">選択してください</option>
                        @foreach(\App\Enums\PlayerClass::cases() as $classOption)
                            <option value="{{ $classOption->value }}" @selected(old('class') === $classOption->value)>
                                {{ $classOption->label() }}
                            </option>
                        @endforeach
                    </select>
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
                    <div class="flex flex-col gap-2 p-3 border rounded bg-white">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notification_via[]" value="mail" 
                                @checked(!old('notification_via') || (is_array(old('notification_via')) && in_array('mail', old('notification_via'))))
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-gray-700 text-sm">メール通知を受け取る <span class="text-xs text-red-500 font-bold">（推奨）</span></span>
                        </label>
                        <p class="text-xs text-gray-500 leading-relaxed pl-6">
                            ※エントリー完了やキャンセル待ちの繰り上げなど、大切な案内が届きます。
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700 transition">
                        登録する
                    </button>
                    <a href="{{ route('user.login') }}" class="text-sm text-gray-600 hover:underline">ログインに戻る</a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>