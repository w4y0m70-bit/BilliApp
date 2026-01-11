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

        <!-- 通知設定 -->
        <div class="mb-4">
            <label class="block font-semibold mb-1">通知設定</label>
            <div class="flex flex-col gap-2">
                @php
                    $adminNotificationTypes = [
                        'event_full' => 'イベントが満員時に通知',
                        // 'new_user' => '新規ユーザー登録時', // 将来の拡張例
                    ];
                @endphp

                @foreach($adminNotificationTypes as $type => $label)
                    <label>
                        <input type="checkbox" name="notify_{{ $type }}_enabled" value="1"
                               {{ $admin->shouldNotify($type) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
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
    </div> 
</div>
@endsection
