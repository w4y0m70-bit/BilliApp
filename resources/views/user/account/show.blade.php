@extends('user.layouts.app')

@section('title', 'アカウント情報')

@section('content')
<div class="max-w-3xl mx-auto px-4">
    {{-- セッションメッセージ表示用 --}}
    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif
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

                <div class="grid grid-cols-2 gap-4 border-b pb-2">
                    <div class="flex flex-col">
                        <span class="text-gray-500 text-sm font-semibold">電話番号</span>
                        <span>{{ $user->phone ?? '－' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-gray-500 text-sm font-semibold">クラス</span>
                        <span class="bg-gray-100 px-2 py-0.5 rounded text-sm">{{ $user->class ?? '－' }}</span>
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
                            'waitlist_updates' => 'キャンセル待ち（繰り上げ・自動終了）',
                            'team_invitations' => 'チーム（招待・承諾・拒否・期限切れ）',
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
                    <!-- @if(!$user->provider_id)
                        <p class="text-[10px] text-red-500 mt-2">
                            ※LINE通知を有効にするには、上記の「LINEと連携する」ボタンから連携を行ってください。
                        </p>
                    @endif -->
                </div>
            </div>

            <div class="flex justify-center pt-4">
                <a href="{{ route('user.account.edit') }}" class="bg-user hover:opacity-90 text-white font-bold py-2 px-10 rounded-full shadow-md transition">
                    登録情報を修正する
                </a>
            </div>
                            {{-- ログイン・認証情報セクション --}}
                <div class="mt-8 bg-blue-50/50 p-5 rounded-xl border border-blue-100 space-y-4">
                    <h3 class="text-sm font-bold text-blue-800 mb-2 flex items-center">
                        <span class="material-symbols-outlined text-sm mr-1">shield</span>
                        ログイン・セキュリティ設定
                    </h3>

                    {{-- メールアドレス --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white p-3 rounded-lg shadow-sm">
                        <div>
                            <span class="text-xs text-gray-500 font-bold block">メールアドレス</span>
                            <div class="flex items-center">
                                <span class="text-gray-900 font-medium">{{ $user->email }}</span>
                                @if($user->hasVerifiedEmail())
                                    <span class="ml-2 text-[10px] text-green-600 flex items-center bg-green-50 px-1.5 py-0.5 rounded border border-green-100">
                                        <span class="material-symbols-outlined text-[12px] mr-0.5">verified</span>認証済み
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button type="button" onclick="openEmailModal()" class="text-user text-xs font-bold border border-user px-3 py-1.5 rounded-full hover:bg-user hover:text-white transition">
                            変更する
                        </button>
                    </div>

                    {{-- パスワード --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white p-3 rounded-lg shadow-sm">
                        <div>
                            <span class="text-xs text-gray-500 font-bold block">パスワード</span>
                            @if(!empty(auth()->user()->password))
                                {{-- パスワード設定済みの場合 --}}
                                <span class="text-gray-400 tracking-tighter">●●●●●●●●●●</span>
                            @else
                                {{-- パスワード未設定の場合 --}}
                                <span class="text-red-400 text-xs font-medium italic text-sm">未設定</span>
                            @endif
                        </div>
                        {{-- パスワード変更画面、またはモーダルへのリンク --}}
                        <a href="{{ route('user.account.password.edit') }}" class="text-user text-xs font-bold border border-user px-3 py-1.5 rounded-full hover:bg-user hover:text-white transition text-center">
                            変更する
                        </a>
                    </div>

                    {{-- LINE連携 --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white p-3 rounded-lg shadow-sm border-l-4 {{ $user->socialAccounts->where('provider', 'line')->isNotEmpty() ? 'border-green-400' : 'border-gray-300' }}">
                        <div class="flex items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/LINE_logo.svg" class="w-4 h-4 mr-2" alt="LINE">
                            <div>
                                <span class="text-xs text-gray-500 font-bold block">LINE連携</span>
                                @if($user->socialAccounts->where('provider', 'line')->isNotEmpty())
                                    <span class="text-green-600 text-xs font-bold">連携済み</span>
                                @else
                                    <span class="text-gray-400 text-xs">未連携</span>
                                @endif
                            </div>
                        </div>
                        @if($user->socialAccounts->where('provider', 'line')->isNotEmpty())
                            <form action="{{ route('user.line.disconnect') }}" method="POST" onsubmit="return confirm('本当にLINE連携を解除しますか？');">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    LINE連携を解除する
                                </button>
                            </form>
                        @else
                            <a href="{{ route('user.line.login') }}" class="bg-[#06C755] text-white text-xs font-bold px-4 py-1.5 rounded-full hover:opacity-90 transition shadow-sm">
                                連携する
                            </a>
                        @endif
                    </div>
                </div>
        </div>
    </div>
</div>

{{-- モーダルとスクリプト（show画面でも必要になります） --}}
@include('user.account._email_modal')
@include('user.account._scripts')
{{-- 隠しフォーム --}}
<form id="line-disconnect-form" action="{{ route('user.line.disconnect') }}" method="POST" class="hidden">
    @csrf @method('POST') 
</form>

@endsection