@extends('user.layouts.app')

@section('title', 'アカウント情報')

@section('content')
<div class="max-w-3xl mx-auto px-4">
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="bg-user px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
                <span class="material-symbols-outlined mr-2">person</span>
                アカウント情報
            </h2>
        </div>

        <div class="p-6 space-y-2">
            <div class="grid grid-cols-1 gap-y-4">
                {{-- 氏名・アカウント名 --}}
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <div class="flex flex-col">
                        <span class="text-gray-500 text-sm font-semibold">氏名 / アカウント名</span>
                        <span class="text-xs text-gray-400">{{ $user->full_name_kana }}</span> {{-- フリガナを表示 --}}
                    </div>
                    <div class="text-right sm:text-right">
                        <span class="font-bold text-lg">{{ $user->full_name }}</span> {{-- アクセサを使用 --}}
                        <span class="text-gray-400 font-normal text-sm ml-1">({{ $user->account_name ?? '未設定' }})</span>
                    </div>
                </div>

                {{-- 基本属性 --}}
                <div class="grid grid-cols-2 gap-4 border-b pb-2">
                    <div class="flex flex-col">
                        <span class="text-gray-500 text-sm font-semibold">性別</span>
                        <span>{{ $user->gender ?? '－' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-500 text-sm font-semibold">誕生日</span>
                        <span>{{ $user->birthday?->format('Y/m/d') ?? '－' }}</span>
                    </div>
                </div>

                {{-- 住所情報：細分化対応 --}}
                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">住所</span>
                    <div class="text-right">
                        @if($user->zip_code)
                            <p class="text-xs text-gray-400">〒{{ $user->zip_code }}</p>
                        @endif
                        <p>
                            {{ $user->prefecture }}{{ $user->city }}<br>
                            {{ $user->address_line }}
                        </p>
                        @if(!$user->prefecture && !$user->address_line)
                            <span class="text-gray-400 italic">未登録</span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">電話番号</span>
                    <span>{{ $user->phone ?? '－' }}</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">メールアドレス</span>
                    <span class="text-blue-600">{{ $user->email }}</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between border-b pb-2">
                    <span class="text-gray-500 text-sm font-semibold">クラス</span>
                    <span class="bg-gray-100 px-2 py-0.5 rounded text-sm">{{ $user->class ?? '－' }}</span>
                </div>
            </div>

            {{-- LINE連携セクション (コンパクト版) --}}
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div class="flex items-center space-x-2">
                    <span class="text-gray-600 text-sm font-medium">LINE連携</span>
                    @if($user->line_id)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            <span class="material-symbols-outlined text-xs mr-1">check_circle</span>
                            連携済み
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                            未連携
                        </span>
                    @endif
                </div>

                <div>
                    @if($user->line_id)
                        {{-- 連携済みの場合：解除をシンプルに --}}
                        <form action="{{ route('user.line.disconnect') }}" method="POST" onsubmit="return confirm('LINE連携を解除しますか？');">
                            @csrf
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 underline">連携解除</button>
                        </form>
                    @else
                        {{-- 未連携の場合：小さいボタン --}}
                        <a href="{{ route('user.line.login') }}" class="inline-flex items-center bg-[#06C755] hover:bg-[#05b34c] text-white text-xs font-bold py-1.5 px-3 rounded shadow-sm transition">
                            <span class="material-symbols-outlined text-sm mr-1.5">chat</span>
                            連携する
                        </a>
                    @endif
                </div>
            </div>
            {{-- 通知設定セクション --}}
            <div class="mt-8 bg-gray-50 p-4 rounded-lg border border-gray-100">
                <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center">
                    <span class="material-symbols-outlined text-sm mr-1">notifications</span>
                    通知設定
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @php
                        $notificationTypes = [
                            'event_published' => '新規イベント公開',
                            'waitlist_promoted' => 'キャンセル待ち繰り上げ',
                            'waitlist_cancelled' => 'キャンセル待ち期限切れ',
                        ];
                        $viaLabels = ['mail' => 'メール', 'line' => 'LINE'];
                    @endphp

                    @foreach($notificationTypes as $type => $label)
                        <div class="bg-white p-3 rounded shadow-sm flex justify-between items-center">
                            <span class="text-sm text-gray-600 font-medium">{{ $label }}</span>
                            <div class="flex gap-1">
                                @php
                                    $activeVias = $user->notificationSettings
                                        ->where('type', $type)
                                        ->where('enabled', true)
                                        ->map(fn($setting) => $viaLabels[$setting->via] ?? $setting->via)
                                        ->toArray();
                                @endphp

                                @if(count($activeVias) > 0)
                                    @foreach($activeVias as $via)
                                        <span class="bg-user/10 text-user text-[10px] px-2 py-0.5 rounded-full font-bold border border-user/20">
                                            {{ $via }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-gray-300 text-[10px] italic font-bold">OFF</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if(!$user->line_id)
                        <p class="text-[10px] text-red-500 mt-2">
                            ※LINE通知を有効にするには、上記の「LINEと連携する」ボタンから連携を行ってください。
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex justify-center pt-4">
                <a href="{{ route('user.account.edit') }}" class="bg-user hover:opacity-90 text-white font-bold py-2 px-10 rounded-full shadow-md transition">
                    登録情報を修正する
                </a>
            </div>
        </div>
    </div>
</div>
@endsection