{{-- resources/views/user/account/show.blade.php --}}
@extends('user.layouts.app')

@section('title', 'アカウント情報')

@section('content')
<div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-4">アカウント情報</h2>

    <div class="space-y-4">
        {{-- 氏名 --}}
        <div class="flex justify-between">
            <span class="font-semibold">氏名</span>
            <span>{{ $user->name }}</span>
        </div>

        {{-- 性別 --}}
        <div class="flex justify-between">
            <span class="font-semibold">性別</span>
            <span>{{ $user->gender ?? '－' }}</span>
        </div>

        {{-- 誕生日 --}}
        <div class="flex justify-between">
            <span class="font-semibold">誕生日</span>
            <span>{{ $user->birthday?->format('Y/m/d') ?? '－' }}</span>
        </div>

        {{-- 住所（編集可） --}}
        <div class="flex justify-between">
            <span class="font-semibold">住所</span>
            <span>{{ $user->address ?? '－' }}</span>
        </div>

        {{-- 電話番号（編集可） --}}
        <div class="flex justify-between">
            <span class="font-semibold">電話番号</span>
            <span>{{ $user->phone ?? '－' }}</span>
        </div>

        {{-- アカウント名（編集可） --}}
        <div class="flex justify-between">
            <span class="font-semibold">アカウント名</span>
            <span>{{ $user->username ?? '－' }}</span>
        </div>

        {{-- メールアドレス（編集可） --}}
        <div class="flex justify-between">
            <span class="font-semibold">メールアドレス</span>
            <span>{{ $user->email }}</span>
        </div>

        {{-- クラス --}}
        <div class="flex justify-between">
            <span class="font-semibold">クラス</span>
            <span>{{ $user->class ?? '－' }}</span>
        </div>

        {{-- 前回ログイン日時 --}}
        <div class="flex justify-between">
            <span class="font-semibold">前回ログイン日時</span>
            <span>{{ $user->last_login_at?->format('Y/m/d H:i') ?? '－' }}</span>
        </div>

        {{-- 通知設定（詳細表示） --}}
        <div class="border-t pt-4">
            <span class="font-semibold block mb-2">通知設定</span>
            <div class="space-y-2">
                @php
                    $notificationTypes = [
                        'event_published' => '新規イベント公開',
                        'waitlist_promoted' => 'キャンセル待ち繰り上げ',
                        'waitlist_cancelled' => 'キャンセル待ち期限切れ',
                    ];
                    $viaLabels = ['mail' => 'メール', 'line' => 'LINE'];
                @endphp

                @foreach($notificationTypes as $type => $label)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $label }}</span>
                        <span>
                            @php
                                // そのタイプで有効（enabled）な通知手段を配列で取得
                                $activeVias = $user->notificationSettings
                                    ->where('type', $type)
                                    ->where('enabled', true)
                                    ->map(fn($setting) => $viaLabels[$setting->via] ?? $setting->via)
                                    ->toArray();
                            @endphp

                            @if(count($activeVias) > 0)
                                {{ implode('・', $activeVias) }}
                            @else
                                <span class="text-gray-400">通知しない</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    {{-- 編集ボタン --}}
    <div class="mt-6 text-right">
        <a href="{{ route('user.account.edit') }}" class="bg-user text-white px-4 py-2 rounded hover:bg-user-dark transition">
            編集
        </a>
    </div>
</div>
@endsection
