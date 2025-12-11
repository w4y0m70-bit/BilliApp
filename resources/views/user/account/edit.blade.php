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
            <input type="email" name="email" class="w-full border p-2 rounded"
                value="{{ old('email', $user->email) }}">
        </div>

        {{-- クラス --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">クラス</label>
            <select name="class" class="w-full border p-2 rounded">
                @php
                    $classes = ['Beginner','C','B','A','Pro'];
                @endphp
                @foreach($classes as $classOption)
                    <option value="{{ $classOption }}" 
                        {{ old('class', $user->class) === $classOption ? 'selected' : '' }}>
                        {{ $classOption }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 通知設定 --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">通知</label>
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
                        <label class="block font-semibold mb-1">{{ $label }}</label>

                        <div class="flex items-center gap-4">
                            <select name="notifications[{{ $type }}][via]" class="border p-2 rounded">
                                @foreach($notificationVias as $key => $name)
                                    <option value="{{ $key }}" 
                                        {{ $user->notificationSettings->firstWhere('type', $type)?->via === $key ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>

                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="notifications[{{ $type }}][enabled]" value="1"
                                    {{ $user->notificationSettings->firstWhere('type', $type)?->enabled ? 'checked' : '' }}>
                                ON
                            </label>
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
