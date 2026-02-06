@extends('user.layouts.guest')
@section('title', 'パスワード再設定')
@section('content')
<div class="max-w-md mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-4 text-center">
        管理者パスワード再設定
    </h1>

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

    <form method="POST" action="{{ route('user.password.update') }}">
        @csrf

        {{-- トークン（必須） --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- メールアドレス（ユーザー入力なし・hidden） --}}
        <input type="hidden" name="email" value="{{ $email }}">

        {{-- 新しいパスワード --}}
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">
                新しいパスワード
            </label>
            <input
                type="password"
                name="password"
                required
                class="w-full border rounded px-3 py-2"
            >
        </div>

        {{-- パスワード確認 --}}
        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1">
                パスワード（確認）
            </label>
            <input
                type="password"
                name="password_confirmation"
                required
                class="w-full border rounded px-3 py-2"
            >
        </div>

        <button
            type="submit"
            class="w-full bg-user text-white py-2 rounded hover:opacity-90 transition">
            パスワードを再設定する
        </button>
    </form>
</div>
@endsection
