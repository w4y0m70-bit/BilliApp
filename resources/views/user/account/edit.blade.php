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

        {{-- 通知先 --}}
        <div class="mb-4">
            <label class="block font-semibold mb-1">通知先</label>
            <select name="notification" class="w-full border p-2 rounded">
                @php
                    $notifications = ['メール','LINE'];
                @endphp
                @foreach($notifications as $notify)
                    <option value="{{ $notify }}" 
                        {{ old('notification', $user->notification) === $notify ? 'selected' : '' }}>
                        {{ $notify }}
                    </option>
                @endforeach
            </select>
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
