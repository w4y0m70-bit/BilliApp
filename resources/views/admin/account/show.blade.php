@extends('admin.layouts.app')

@section('title', 'アカウント情報')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="bg-admin px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
                <span class="material-symbols-outlined mr-2">account_circle</span>
                アカウント情報
            </h2>
        </div>

        <div class="p-6 space-y-6">
            {{-- 基本情報セクション --}}
            <div class="grid grid-cols-1 gap-y-4">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">管理者ID</span>
                    <span class="font-mono">{{ $admin->admin_id }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">店舗名 / 主催者名</span>
                    <span class="font-bold text-lg">{{ $admin->name }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">担当者名</span>
                    <span>{{ $admin->manager_name ?? '未設定' }}</span>
                </div>
                
                {{-- 住所表示：細分化したカラムを統合して表示 --}}
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">所在地</span>
                    <div class="text-right">
                        @if($admin->zip_code)
                            <p class="text-xs text-gray-400">〒{{ $admin->zip_code }}</p>
                        @endif
                        <p>
                            {{ $admin->prefecture }}{{ $admin->city }}<br>
                            {{ $admin->address_line }}
                        </p>
                        @if(!$admin->prefecture && !$admin->address_line)
                            <span class="text-gray-400 italic">未登録</span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">電話番号</span>
                    <span>{{ $admin->phone ?? '未設定' }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">メールアドレス</span>
                    <span class="text-blue-600">{{ $admin->email }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">最終ログイン</span>
                    <span class="text-sm text-gray-600">{{ $admin->last_login_at ? $admin->last_login_at->format('Y/m/d H:i') : '記録なし' }}</span>
                </div>
            </div>

            {{-- 通知設定セクション --}}
            <div class="mt-8 bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                    <span class="material-symbols-outlined text-sm mr-1">notifications</span>
                    通知設定
                </h3>
                <div class="space-y-3">
                    @php
                        $adminNotificationTypes = [
                            'event_full' => 'イベント満員時の通知',
                        ];
                        $viaLabels = ['mail' => 'メール', 'line' => 'LINE'];
                    @endphp
                    
                    @foreach($adminNotificationTypes as $type => $label)
                        <div class="flex justify-between items-center bg-white p-3 rounded border border-gray-200">
                            <span class="text-sm text-gray-600">{{ $label }}</span>
                            <div class="flex gap-2">
                                @php
                                    $activeVias = $admin->notificationSettings
                                        ->where('type', $type)
                                        ->where('enabled', true)
                                        ->map(fn($setting) => $viaLabels[$setting->via] ?? $setting->via)
                                        ->toArray();
                                @endphp

                                @if(count($activeVias) > 0)
                                    @foreach($activeVias as $via)
                                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full font-bold">
                                            {{ $via }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-gray-400 text-xs italic">通知OFF</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-center pt-4">
                <a href="{{ route('admin.account.edit') }}" 
                   class="bg-admin hover:bg-admin-dark text-white font-bold py-2 px-8 rounded-full shadow transition-all">
                    情報を編集する
                </a>
            </div>
        </div>
    </div>
</div>
@endsection