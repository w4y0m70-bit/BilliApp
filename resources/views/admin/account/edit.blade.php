@extends('admin.layouts.app')
@section('title', 'アカウント情報編集')
@section('content')
<div class="bg-white shadow p-6 rounded">
    <h2 class="text-xl font-bold mb-4">アカウント情報編集</h2>

    <form action="{{ route('admin.account.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <!-- 管理者ID -->
        <div class="mb-4">
            <label class="block font-semibold">管理者ID</label>
            <input type="text" name="admin_id" class="border rounded w-full p-2"
                   value="{{ old('admin_id', $admin->admin_id) }}">
        </div>

        <!-- 店舗名 -->
        <div class="mb-4">
            <label class="block font-semibold">店舗名（管理者名）</label>
            <input type="text" name="name" class="border rounded w-full p-2"
                   value="{{ old('name', $admin->name) }}">
        </div>

        <!-- 住所 -->
        <div class="mb-4">
            <label class="block font-semibold">住所</label>
            <input type="text" name="address" class="border rounded w-full p-2"
                   value="{{ old('address', $admin->address) }}">
        </div>

        <!-- 電話番号 -->
        <div class="mb-4">
            <label class="block font-semibold">電話番号</label>
            <input type="text" name="phone" class="border rounded w-full p-2"
                   value="{{ old('phone', $admin->phone) }}">
        </div>

        <!-- メールアドレス -->
        <div class="mb-4">
            <label class="block font-semibold">メールアドレス</label>
            <input type="email" name="email" class="border rounded w-full p-2"
                   value="{{ old('email', $admin->email) }}">
        </div>

        <!-- 通知手段 -->
        <div class="mb-4">
            <label class="block font-semibold mb-1">通知手段</label>
            <select name="notification_methods[]" multiple class="border rounded w-full p-2">
                <option value="mail" {{ in_array('mail', $admin->notificationMethods()) ? 'selected' : '' }}>メール</option>
                <option value="line" {{ in_array('line', $admin->notificationMethods()) ? 'selected' : '' }}>LINE</option>
            </select>
            <p class="text-sm text-gray-500 mt-1">通知手段を選択してください（複数可）</p>
        </div>

        <!-- 通知対象 -->
        <div class="mb-4">
            <label class="block font-semibold mb-1">通知設定</label>
            <div class="flex flex-col gap-2">
                <label>
                    <input type="checkbox" name="notify_event_full_enabled" value="1"
                           {{ $admin->shouldNotify('event_full') ? 'checked' : '' }}>
                    満員時に通知
                </label>
                <!-- 将来追加される通知項目はここにチェックボックス追加 -->
            </div>
        </div>

        <button type="submit" class="bg-admin text-white px-4 py-2 rounded">
            更新する
        </button>

        <a href="{{ route('admin.account.show') }}"
           class="ml-3 text-gray-700 underline">
            戻る
        </a>
    </form>
</div>
@endsection
