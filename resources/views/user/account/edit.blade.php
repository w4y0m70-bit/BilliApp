@extends('user.layouts.app')

@section('title', 'プロフィール編集')

@section('content')
{{-- 郵便番号自動入力スクリプト --}}
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

<div class="max-w-2xl mx-auto px-4">
    <div class="bg-white shadow rounded-xl overflow-hidden p-6">
        <h2 class="text-xl font-bold mb-6 flex items-center border-b pb-2">
            <span class="material-symbols-outlined mr-2">edit</span>
            プロフィール編集
        </h2>

        {{-- バリデーションエラー表示 --}}
        @if ($errors->any())
            <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.account.update') }}" method="POST" class="h-adr">
            @csrf
            @method('PATCH')
            
            <span class="p-country-name" style="display:none;">Japan</span>

            <div class="space-y-6">
                {{-- 氏名セクション (追加) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">氏名</label>
                        <div class="flex gap-2">
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" 
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-user/50 focus:outline-none" placeholder="姓" required>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" 
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-user/50 focus:outline-none" placeholder="名" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">フリガナ</label>
                        <div class="flex gap-2">
                            <input type="text" name="last_name_kana" value="{{ old('last_name_kana', $user->last_name_kana) }}" 
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-user/50 focus:outline-none" placeholder="セイ" required pattern="^[ァ-ヶー]+$">
                            <input type="text" name="first_name_kana" value="{{ old('first_name_kana', $user->first_name_kana) }}" 
                                class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-user/50 focus:outline-none" placeholder="メイ" required pattern="^[ァ-ヶー]+$">
                        </div>
                    </div>
                </div>

                {{-- アカウント名 (username -> account_name に修正) --}}
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">アカウント名</label>
                    <input type="text" name="account_name" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-user/50 focus:outline-none"
                            value="{{ old('account_name', $user->account_name) }}" placeholder="アプリ内での表示名">
                </div>

                {{-- メールアドレス --}}
                <div class="mb-4">
                    <label class="block font-semibold mb-1">メールアドレス</label>
                    <input type="email" name="email" 
                        value="{{ old('email', $user->email) }}"
                        readonly 
                        class="w-full border p-2 rounded mt-1 block bg-gray-100 border-gray-300 shadow-sm focus:ring-0 cursor-not-allowed">
                    <!-- <p class="text-xs text-gray-500 mt-1">※メールアドレスは変更できません。</p> -->
                </div>

                {{-- 住所セクション（細分化） --}}
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <label class="block text-sm font-bold mb-3 text-user flex items-center">
                        <span class="material-symbols-outlined text-sm mr-1">location_on</span>
                        お住まいの地域
                    </label>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="text-xs text-gray-500 font-bold">郵便番号 <span class="text-[10px] font-normal">(ハイフンなし)</span></label>
                            <input type="text" name="zip_code" value="{{ old('zip_code', $user->zip_code) }}" 
                                class="p-postal-code w-full border rounded px-3 py-2 focus:ring-1 focus:ring-user" placeholder="1234567">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 font-bold">都道府県</label>
                            <input type="text" name="prefecture" value="{{ old('prefecture', $user->prefecture) }}" 
                                class="p-region w-full border rounded px-3 py-2 bg-gray-100" readonly>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 font-bold">市区町村</label>
                            <input type="text" name="city" value="{{ old('city', $user->city) }}" 
                                class="p-locality w-full border rounded px-3 py-2 focus:ring-1 focus:ring-user">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 font-bold">番地・建物名</label>
                            <input type="text" name="address_line" value="{{ old('address_line', $user->address_line) }}" 
                                class="p-street-address p-extended-address w-full border rounded px-3 py-2 focus:ring-1 focus:ring-user">
                        </div>
                    </div>
                </div>

                {{-- 電話番号 --}}
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">電話番号</label>
                    <input type="text" name="phone" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-user/50 focus:outline-none"
                           value="{{ old('phone', $user->phone) }}" placeholder="09012345678">
                </div>

                {{-- クラス選択 --}}
                <div>
                    <label class="block text-sm font-semibold mb-1 text-gray-700">クラス</label>
                    <select name="class" class="w-full border rounded px-3 py-2 bg-white focus:ring-2 focus:ring-user/50 focus:outline-none">
                        @foreach(\App\Enums\PlayerClass::cases() as $classOption)
                            <option value="{{ $classOption->value }}" 
                                @selected(old('class', $user->class instanceof \App\Enums\PlayerClass ? $user->class->value : $user->class) === $classOption->value)>
                                {{ $classOption->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 通知設定 --}}
                <div class="pt-4 border-t">
                    <label class="block font-bold mb-4 text-gray-700 text-sm">通知設定</label>
                    @php
                        $notificationTypes = [
                            'event_published' => '新規イベント公開',
                            'waitlist_promoted' => 'キャンセル待ち繰り上げ',
                            'waitlist_cancelled' => 'キャンセル待ち期限切れ',
                        ];
                        $notificationVias = ['mail' => 'メール', 'line' => 'LINE'];
                    @endphp

                    <div class="space-y-4">
                        @foreach($notificationTypes as $type => $label)
                            <div>
                                <span class="block text-xs font-bold text-gray-500 mb-2">{{ $label }}</span>
                                <div class="flex gap-6 pl-2">
                                    @foreach($notificationVias as $viaKey => $viaLabel)
                                        @php
                                            $isEnabled = $user->notificationSettings
                                                ->where('type', $type)
                                                ->where('via', $viaKey)
                                                ->where('enabled', true)
                                                ->isNotEmpty();
                                        @endphp
                                        <label class="inline-flex items-center cursor-pointer group">
                                            <input type="checkbox" 
                                                name="notifications[{{ $type }}][{{ $viaKey }}]" 
                                                value="1"
                                                @checked(old("notifications.$type.$viaKey", $isEnabled))
                                                class="rounded border-gray-300 text-user shadow-sm focus:ring-user">
                                            <span class="ml-2 text-sm text-gray-600 group-hover:text-user transition">{{ $viaLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t flex items-center gap-4">
                <button type="submit" class="bg-user hover:opacity-90 text-white font-bold py-2 px-8 rounded-full shadow-md transition-all">
                    更新する
                </button>
                <a href="{{ route('user.account.show') }}" class="text-sm text-gray-500 hover:text-gray-700 underline">
                    戻る
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

