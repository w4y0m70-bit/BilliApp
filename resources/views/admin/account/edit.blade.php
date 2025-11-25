@extends('admin.layouts.app')

@section('content')
<div class="bg-white shadow p-6 rounded">
    <h2 class="text-xl font-bold mb-4">アカウント情報編集</h2>

    <form action="{{ route('admin.account.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label class="block font-semibold">管理者ID</label>
            <input type="text" name="name" class="border rounded w-full p-2"
                   value="{{ old('admin_id', $admin->admin_id) }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">店舗名（管理者名）</label>
            <input type="text" name="name" class="border rounded w-full p-2"
                   value="{{ old('name', $admin->name) }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">住所</label>
            <input type="text" name="address" class="border rounded w-full p-2"
                   value="{{ old('address', $admin->address) }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">電話番号</label>
            <input type="text" name="phone" class="border rounded w-full p-2"
                   value="{{ old('phone', $admin->phone) }}">
        </div>

        <div class="mb-4">
            <label class="block font-semibold">メールアドレス</label>
            <input type="email" name="email" class="border rounded w-full p-2"
                   value="{{ old('email', $admin->email) }}">
        </div>

        <button class="bg-admin text-white px-4 py-2 rounded">
            更新する
        </button>

        <a href="{{ route('admin.account') }}"
           class="ml-3 text-gray-700 underline">
            戻る
        </a>
    </form>
</div>
@endsection
