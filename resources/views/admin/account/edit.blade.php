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
            <input type="email" name="email"
                   value="{{ old('email', $admin->email) }}"
                   readonly
                     class="border rounded w-full p-2w-full border p-2 rounded mt-1 block bg-gray-100 border-gray-300 shadow-sm focus:ring-0 cursor-not-allowed">
        </div>

        <!-- 通知設定 -->
        <div class="mb-4">
            <label class="block font-semibold mb-4 border-b pb-2">通知設定</label>
            
            @php
                // 通知の種類
                $adminNotificationTypes = [
                    'event_full' => 'イベントが満員時に通知',
                    // 'new_user' => '新規ユーザー登録時',
                ];
                // 通知の手段
                $notificationVias = [
                    'mail' => 'メール',
                    'line' => 'LINE'
                ];
            @endphp

            @foreach($adminNotificationTypes as $type => $label)
                <div class="mb-4">
                    <span class="block font-medium mb-2 text-sm text-gray-700">{{ $label }}</span>
                    <div class="flex gap-6">
                        @foreach($notificationVias as $viaKey => $viaLabel)
                            @php
                                // 管理者の設定から、該当する種別と手段が有効かチェック
                                // ※ $admin->shouldNotify($type, $viaKey) のようなメソッドがある想定、
                                // もしくは 1つ目のコードと同様の where 句での判定に調整してください
                                $isChecked = $admin->notificationSettings
                                    ->where('type', $type)
                                    ->where('via', $viaKey)
                                    ->where('enabled', true)
                                    ->isNotEmpty();
                            @endphp
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                    name="notifications[{{ $type }}][{{ $viaKey }}]" 
                                    value="1"
                                    @checked(old("notifications.$type.$viaKey", $isChecked))
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">{{ $viaLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
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
