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

        @if (session('warning'))
            <div class="mb-4 flex items-center p-4 text-amber-800 border-t-4 border-amber-300 bg-amber-50" role="alert">
                <span class="material-symbols-outlined mr-2">warning</span>
                <div class="text-sm font-medium">
                    {{ session('warning') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 flex items-center p-4 text-red-800 border-t-4 border-red-300 bg-red-50" role="alert">
                <span class="material-symbols-outlined mr-2">error</span>
                <div class="text-sm font-medium">
                    {{ session('error') }}
                </div>
            </div>
        @endif
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

                {{-- メールアドレス --}}
                <div class="mb-4">
                    <label class="block font-semibold mb-1 text-gray-700">メールアドレス</label>
                    <div class="flex items-start gap-2">
                        <div class="flex-grow">
                            <input type="email" id="email_input" name="email" 
                                value="{{ old('email', $user->email) }}"
                                class="w-full border p-2 rounded block shadow-sm focus:ring-user focus:border-user {{ $user->email ? 'bg-white' : 'bg-yellow-50' }}"
                                placeholder="example@mail.com">
                        </div>
                        
                        {{-- 専用ボタン --}}
                        <div>
                            @if(!$user->email)
                                <button type="button" onclick="updateEmailOnly()" class="bg-user text-white text-xs px-4 py-2.5 rounded shadow-sm hover:opacity-90 whitespace-nowrap">
                                    登録
                                </button>
                            @elseif(!$user->hasVerifiedEmail())
                                <button type="button" onclick="event.preventDefault(); document.getElementById('verification-form').submit();" class="bg-amber-500 text-white text-xs px-4 py-2.5 rounded shadow-sm hover:bg-amber-600 whitespace-nowrap">
                                    認証メールを送信
                                </button>
                            @else
                                <button type="button" onclick="updateEmailOnly()" class="bg-gray-600 text-white text-xs px-4 py-2.5 rounded shadow-sm hover:bg-gray-700 whitespace-nowrap">
                                    変更
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- ステータス表示 --}}
                    @if($user->email)
                        <div class="mt-1">
                            @if($user->hasVerifiedEmail())
                                <span class="text-green-600 text-[10px] flex items-center">
                                    <span class="material-symbols-outlined text-xs mr-1">check_circle</span>認証済み。このアドレスでログイン可能です。
                                </span>
                            @else
                                <span class="text-amber-600 text-[10px] flex items-center">
                                    <span class="material-symbols-outlined text-xs mr-1">pending</span>未認証。届いたメールのリンクをクリックしてください。
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- パスワード設定 (新規追加) --}}
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mt-6">
                    <label class="block text-sm font-bold mb-3 text-blue-800 flex items-center">
                        <span class="material-symbols-outlined text-sm mr-1">lock</span>
                        ログインパスワード（メールアドレスでログインする場合に必要です）
                    </label>
                    @if(empty($user->password))
                        {{-- パスワードが未設定（LINE登録直後など） --}}
                        <div class="flex items-center p-1 mb-2 text-amber-800 bg-amber-50 rounded-lg border border-amber-200">
                            <span class="material-symbols-outlined mr-2 text-sm">priority_high</span>
                            <p class="text-xs">
                                現在パスワードが設定されていません。メールアドレスでログインするには設定が必要です。
                            </p>
                        </div>
                    @else
                        {{-- パスワード設定済み --}}
                        <div class="flex items-center p-1 mb-2 text-green-800 bg-green-50 rounded-lg border border-green-200">
                            <span class="material-symbols-outlined mr-2 text-sm">check_circle</span>
                            <p class="text-xs">
                                パスワードは設定済みです。変更したい場合は入力してください。
                            </p>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500 font-bold">新しいパスワード</label>
                            <input type="password" name="password" 
                                class="w-full border rounded px-3 py-2 focus:ring-1 focus:ring-user" placeholder="8文字以上">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 font-bold">パスワード(確認)</label>
                            <input type="password" name="password_confirmation" 
                                class="w-full border rounded px-3 py-2 focus:ring-1 focus:ring-user" placeholder="もう一度入力">
                        </div>
                    </div>
                    <!-- <p class="text-[10px] text-blue-600 mt-2">※メールアドレスでログインしたい場合は設定してください。</p> -->
                </div>

                {{-- LINE連携 --}}
                <div class="bg-green-50 p-4 rounded-lg border border-green-200 mt-6">
                    <label class="block text-sm font-bold mb-3 text-gray-700 flex items-center">
                        <img src="{{ asset('images/LINE_Brand_icon.png') }}" class="w-4 h-4 mr-1">
                        LINE連携設定
                    </label>

                    @if($user->socialAccounts->where('provider', 'line')->isNotEmpty())
                        <div class="bg-white p-3 rounded border border-green-200">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-green-700 font-medium flex items-center">
                                    <span class="material-symbols-outlined mr-1 text-sm">check_circle</span>
                                    LINEと連携しています
                                </span>

                                {{-- 解除条件の判定 --}}
                                @if(!empty($user->email) && !empty($user->password))
                                    <button type="button" 
                                            onclick="if(confirm('LINE連携を解除しますか？')) document.getElementById('line-disconnect-form').submit();"
                                            class="text-xs text-red-500 hover:underline">
                                        連携を解除する
                                    </button>
                                @endif
                            </div>

                            {{-- メール/パスワードが未設定の場合の警告 --}}
                            <!-- @if(empty($user->email) || empty($user->password))
                                <div class="mt-3 p-2 bg-green-50 border border-green-100 rounded text-[11px] text-green-600">
                                    <p class="font-bold mb-1">【重要】連携を解除できません</p>
                                    <p>メールアドレスとパスワードが設定されていないため、今連携を解除すると次回からログインできなくなります。解除を希望される場合は、先に上記の項目を設定して保存してください。</p>
                                </div>
                            @endif -->
                        </div>
                    @else
                        {{-- 未連携時の表示はそのまま --}}
                        <div class="bg-white p-3 rounded border border-gray-200">
                            <p class="text-xs text-gray-500 mb-3">LINEと連携すると、ログインが簡単になり、通知をLINEで受け取れるようになります。</p>
                            <x-user.line-auth-button type="link" class="!w-auto !py-1.5 !text-xs" />
                        </div>
                    @endif
                </div>
                
                {{-- 通知設定 --}}
                <div class="pt-4 border-t">
                    <label class="block font-bold mb-4 text-gray-700 text-sm">通知設定</label>
                    @php
                        $notificationTypes = [
                            'event_published' => '新規イベント公開',
                            'waitlist_updates' => 'キャンセル待ち（繰り上げ・自動終了）',
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
                                            // 現在の設定状況を確認
                                            $isEnabled = $user->notificationSettings
                                                ->where('type', $type)
                                                ->where('via', $viaKey)
                                                ->where('enabled', true)
                                                ->isNotEmpty();

                                            // 選択可能かどうかの判定
                                            $isDisabled = false;
                                            $reason = '';

                                            if ($viaKey === 'mail' && !$user->hasVerifiedEmail()) {
                                                $isDisabled = true;
                                                $reason = '(メール認証後に利用可)';
                                            }

                                            if ($viaKey === 'line' && $user->socialAccounts->where('provider', 'line')->isEmpty()) {
                                                $isDisabled = true;
                                                $reason = '(LINE連携後に利用可)';
                                            }
                                        @endphp

                                        <label class="inline-flex items-center {{ $isDisabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer group' }}">
                                            <input type="checkbox" 
                                                name="notifications[{{ $type }}][{{ $viaKey }}]" 
                                                value="1"
                                                @checked(old("notifications.$type.$viaKey", $isEnabled))
                                                {{ $isDisabled ? 'disabled' : '' }}
                                                class="rounded border-gray-300 text-user shadow-sm focus:ring-user {{ $isDisabled ? 'bg-gray-100' : '' }}">
                                            
                                            <span class="ml-2 text-sm text-gray-600">
                                                {{ $viaLabel }}
                                                @if($isDisabled)
                                                    <span class="text-[10px] text-gray-400 block md:inline md:ml-1">{{ $reason }}</span>
                                                @endif
                                            </span>
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
                @if($user->last_name)
                    <a href="{{ route('user.account.show') }}" class="text-gray-500 text-sm">
                        ＜ 戻る
                    </a>
                @else
                    {{-- 名前がない（新規登録直後）は、戻るボタンの代わりに案内を出すか、何も出さない --}}
                    <span class="text-red-500 text-xs font-bold">
                        ※ プロフィールの初期設定を完了させてください
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 認証メール送信用の隠しフォーム --}}
<form id="verification-form" method="POST" action="{{ route('user.verification.send') }}" class="hidden">
    @csrf
</form>
{{-- LINE認証解除用の隠しフォーム --}}
<form id="line-disconnect-form" action="{{ route('user.line.disconnect') }}" method="POST" style="display: none;">
    @csrf
    {{-- メソッドを DELETE にしている場合はコントローラー側も合わせる必要があります --}}
    @method('POST') 
</form>

<script>
function updateEmailOnly() {
    const email = document.getElementById('email_input').value;
    if(!email) {
        alert('メールアドレスを入力してください。');
        return;
    }
    if(confirm('メールアドレスを更新し、認証メールを送信しますか？')) {
        // メインのフォームを送信する
        document.querySelector('form.h-adr').submit();
    }
}
</script>
@endsection

