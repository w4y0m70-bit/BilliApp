@extends('admin.layouts.app')

@section('title', 'アカウント情報')

@section('content')
<div class="bg-white shadow p-6 rounded">
    <h2 class="text-xl font-bold mb-4">アカウント情報</h2>
    <div class="space-y-4">
        <div class="flex justify-between">
            <span class="font-semibold">管理者ID</span>
            <span>{{ $admin->admin_id }}</span>
        </div>
        <div class="flex justify-between">
            <span class="font-semibold">店舗名</span>
            <span>{{ $admin->name }}</span>
        </div>
        <div class="flex justify-between">
            <span class="font-semibold">住所</span>
            <span>{{ $admin->address }}</span>
        </div>
        <div class="flex justify-between">            
            <span class="font-semibold">電話番号</span>
            <span>{{ $admin->phone }}</span>
        </div>
        <div class="flex justify-between">
            <span class="font-semibold">メール</span>
            <span>{{ $admin->email }}</span>
        </div>
        <!-- <div class="flex justify-between">
            <span class="font-semibold">サブスク期限</span>
            <span>{{ $admin->subscription_until }}</span>
        </div> -->
        <div class="flex justify-between">
            <span class="font-semibold">最終ログイン</span>
            <span>{{ $admin->last_login_at }}</span>
        </div>
        <div class="border-t pt-4">
            <span class="font-semibold block mb-2">通知設定</span>
            <div class="space-y-2">
                @php
                    $adminNotificationTypes = [
                        'event_full' => 'イベントが満員時に通知',
                        // 'new_user' => '新規ユーザー登録時', // 将来の拡張例
                    ];
                    $viaLabels = ['mail' => 'メール', 'line' => 'LINE'];
                @endphp
                
                @foreach($adminNotificationTypes as $type => $label)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $label }}</span>
                        <span>
                            @php
                                // そのタイプで有効（enabled）な通知手段を配列で取得
                                $activeVias = $admin->notificationSettings
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

                <!-- @foreach($adminNotificationTypes as $type => $label)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $label }}</span>
                        <span>
                            @php
                                // admin_id に紐づく設定を取得
                                $activeVias = $admin->notificationSettings
                                    ->where('type', $type)
                                    ->where('enabled', true)
                                    ->map(fn($setting) => $viaLabels[$setting->via] ?? $setting->via)
                                    ->toArray();
                            @endphp

                            @if(count($activeVias) > 0)
                                <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded text-sm">
                                    {{ implode('・', $activeVias) }}
                                </span>
                            @else
                                <span class="text-gray-400">通知しない</span>
                            @endif
                        </span>
                    </div>
                @endforeach -->
            </div>
        </div>
    <a href="{{ route('admin.account.edit') }}" 
       class="mt-4 inline-block bg-admin text-white px-4 py-2 rounded">
        編集する
    </a>
</div>
@endsection
