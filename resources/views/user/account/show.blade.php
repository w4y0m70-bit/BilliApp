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

        {{-- 通知先（編集可） --}}
        <div class="flex justify-between">
            <span class="font-semibold">通知先</span>
            <span>{{ $user->notification_method ?? '－' }}</span>
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
