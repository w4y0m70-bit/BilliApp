@extends('admin.layouts.app')

@section('title', 'アカウント情報編集')

@section('content')
{{-- yubinbango.js の読み込み --}}
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>

<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow p-6 rounded-xl">
        <h2 class="text-xl font-bold mb-6 border-b pb-2 flex items-center">
            <span class="material-symbols-outlined mr-2">edit_note</span>
            アカウント情報編集
        </h2>

        {{-- バリデーションエラーの表示 --}}
        @if ($errors->any())
            <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.account.update') }}" method="POST" class="h-adr">
            @csrf
            @method('PATCH')
            
            {{-- 国名指定（yubinbango用） --}}
            <span class="p-country-name" style="display:none;">Japan</span>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1 text-gray-700">管理者ID</label>
                <input type="text" name="admin_id" class="border rounded w-full p-2 focus:ring-2 focus:ring-admin focus:outline-none"
                       value="{{ old('admin_id', $admin->admin_id) }}">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1 text-gray-700">店舗名（管理者名）</label>
                <input type="text" name="name" class="border rounded w-full p-2 focus:ring-2 focus:ring-admin focus:outline-none"
                       value="{{ old('name', $admin->name) }}" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1 text-gray-700">担当者名</label>
                <input type="text" name="manager_name" class="border rounded w-full p-2 focus:ring-2 focus:ring-admin focus:outline-none"
                       value="{{ old('manager_name', $admin->manager_name) }}">
            </div>

            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-sm font-bold mb-3 text-admin flex items-center">
                    <span class="material-symbols-outlined text-sm mr-1">location_on</span>
                    所在地
                </label>
                
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-xs text-gray-500">郵便番号 (ハイフンなし)</label>
                        <input type="text" name="zip_code" value="{{ old('zip_code', $admin->zip_code) }}" 
                            class="p-postal-code w-full border rounded px-3 py-2 focus:ring-1 focus:ring-admin" placeholder="1234567">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500">都道府県</label>
                        <input type="text" name="prefecture" value="{{ old('prefecture', $admin->prefecture) }}" 
                            class="p-region w-full border rounded px-3 py-2 bg-white" readonly>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500">市区町村</label>
                        <input type="text" name="city" value="{{ old('city', $admin->city) }}" 
                            class="p-locality w-full border rounded px-3 py-2 focus:ring-1 focus:ring-admin">
                    </div>

                    <div>
                        <label class="text-xs text-gray-500">番地・建物名</label>
                        <input type="text" name="address_line" value="{{ old('address_line', $admin->address_line) }}" 
                            class="p-street-address p-extended-address w-full border rounded px-3 py-2 focus:ring-1 focus:ring-admin">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1 text-gray-700">電話番号</label>
                <input type="text" name="phone" class="border rounded w-full p-2 focus:ring-2 focus:ring-admin focus:outline-none"
                       value="{{ old('phone', $admin->phone) }}">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-1 text-gray-700">メールアドレス（変更不可）</label>
                <input type="email" name="email"
                       value="{{ old('email', $admin->email) }}"
                       readonly
                       class="border rounded w-full p-2 bg-gray-100 border-gray-300 text-gray-500 cursor-not-allowed shadow-sm">
            </div>

            <div class="mb-8">
                <label class="block font-bold mb-4 border-b pb-2 text-gray-700 text-sm">通知設定</label>
                
                @php
                    $adminNotificationTypes = ['event_full' => 'イベントが満員時に通知'];
                    $notificationVias = ['mail' => 'メール', 'line' => 'LINE'];
                @endphp

                @foreach($adminNotificationTypes as $type => $label)
                    <div class="mb-4">
                        <span class="block font-medium mb-2 text-sm text-gray-600">{{ $label }}</span>
                        <div class="flex gap-6">
                            @foreach($notificationVias as $viaKey => $viaLabel)
                                @php
                                    $isChecked = $admin->notificationSettings
                                        ->where('type', $type)
                                        ->where('via', $viaKey)
                                        ->where('enabled', true)
                                        ->isNotEmpty();
                                @endphp
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="checkbox" 
                                        name="notifications[{{ $type }}][{{ $viaKey }}]" 
                                        value="1"
                                        @checked(old("notifications.$type.$viaKey", $isChecked))
                                        class="rounded border-gray-300 text-admin shadow-sm focus:ring-admin">
                                    <span class="ml-2 text-sm text-gray-600 group-hover:text-admin transition">{{ $viaLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center border-t pt-6">
                <button type="submit" class="bg-admin hover:bg-admin-dark text-white px-8 py-2 rounded-full font-bold shadow-md transition-all">
                    更新する
                </button>
                <a href="{{ route('admin.account.show') }}" class="ml-6 text-sm text-gray-500 hover:text-gray-700 underline">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</div>
@endsection