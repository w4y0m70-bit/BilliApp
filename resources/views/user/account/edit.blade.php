@extends('user.layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">プロフィール編集</h2>

    <form action="{{ route('user.account.update') }}" method="POST">
        @csrf
        @method('PATCH')

        {{-- 住所 --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">住所</label>
            <input type="text" name="address" class="w-full border p-2 rounded"
                value="{{ old('address', $user->address) }}">
        </div>

        {{-- 電話番号 --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">電話番号</label>
            <input type="text" name="phone" class="w-full border p-2 rounded"
                value="{{ old('phone', $user->phone) }}">
        </div>

        {{-- アカウント名 --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">アカウント名</label>
            <input type="text" name="account_name" class="w-full border p-2 rounded"
                value="{{ old('account_name', $user->account_name) }}">
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

        {{-- クラス --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">クラス</label>
            <select name="class" class="w-full border p-2 rounded">
                @foreach(\App\Enums\PlayerClass::cases() as $classOption)
                    <option value="{{ $classOption->value }}" 
                        {{ old('class', $user->class instanceof \App\Enums\PlayerClass ? $user->class->value : $user->class) === $classOption->value ? 'selected' : '' }}>
                        {{ $classOption->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 通知設定 --}}
        <div class="mb-4">
            <label class="block font-semibold mb-4 border-b pb-2">通知設定</label>
            @php
                $notificationTypes = [
                    'event_published' => '新規イベント公開',
                    'waitlist_promoted' => 'キャンセル待ち繰り上げ',
                    'waitlist_cancelled' => 'キャンセル待ち期限切れ',
                ];
                $notificationVias = ['mail' => 'メール', 'line' => 'LINE'];
            @endphp

            @foreach($notificationTypes as $type => $label)
                <div class="mb-4">
                    <span class="block font-medium mb-2 text-sm text-gray-700">{{ $label }}</span>
                    <div class="flex gap-6">
                        @foreach($notificationVias as $viaKey => $viaLabel)
                            @php
                                // 現在のユーザーの設定の中に、該当する type と via が存在し、かつ enabled かどうかを確認
                                $isEnabled = $user->notificationSettings
                                    ->where('type', $type)
                                    ->where('via', $viaKey)
                                    ->where('enabled', true)
                                    ->isNotEmpty();
                            @endphp
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                    name="notifications[{{ $type }}][{{ $viaKey }}]" 
                                    value="1"
                                    @checked(old("notifications.$type.$viaKey", $isEnabled))
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">{{ $viaLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>


        <div class="mt-6 flex items-center gap-4">
    {{-- 更新ボタン --}}
    <button type="submit" class="bg-user text-white px-4 py-2 rounded hover:bg-user-dark transition">
        更新する
    </button>

    {{-- 戻るリンク --}}
    <a href="{{ route('user.account.show') }}" class="text-gray-600 hover:underline">
        戻る
    </a>
</div>
    </form>
</div>
@endsection
