@extends('user.layouts.app')

@section('title', 'ログイン')

@section('content')
<div class="max-w-md mx-auto bg-white shadow rounded-lg p-6 mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center">ユーザーログイン</h2>

    <form method="POST" action="{{ route('user.login.post') }}">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 mb-1">メールアドレス</label>
            <input type="email" name="email" class="w-full border p-2 rounded">
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 mb-1">パスワード</label>
            <input type="password" name="password" class="w-full border p-2 rounded">
        </div>

        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
            ログイン
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="#" class="text-sm text-blue-600 hover:underline">パスワードをお忘れの方はこちら</a>
    </div>
</div>
@endsection
