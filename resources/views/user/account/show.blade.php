@extends('user.layouts.app')
@section('title', 'アカウント情報')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-8">
        {{-- メッセージ表示（管理者側と統一） --}}
        @if (session('success'))
            <div
                class="mb-6 flex items-center bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                <span class="material-symbols-outlined mr-2 text-green-500">check_circle</span>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- メインカード --}}
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
            {{-- ヘッダー：管理者側と同じ構成 --}}
            <div class="border-b border-gray-100 px-8 py-6 flex justify-between items-center bg-gray-50/50">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="material-symbols-outlined mr-2 text-user text-3xl">account_circle</span>
                    アカウント情報
                </h2>
                <a href="{{ route('user.account.edit') }}"
                    class="flex items-center bg-white border border-gray-200 text-gray-700 hover:text-user hover:border-user font-bold py-2 px-4 rounded-xl shadow-sm transition-all text-sm">
                    <span class="material-symbols-outlined text-sm mr-1">edit</span>
                    編集
                </a>
            </div>

            <div class="p-8 space-y-10">
                {{-- 1. 基本情報セクション --}}
                <section>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                        <span class="w-1.5 h-1.5 bg-user rounded-full mr-2"></span>
                        基本情報
                    </h3>
                    <div class="grid grid-cols-1 gap-y-5">
                        {{-- 氏名 --}}
                        <div class="flex flex-col sm:flex-row sm:items-center py-1">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">氏名（アカウント名）</span>
                            <div class="sm:w-2/3 flex flex-col">
                                <span
                                    class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $user->full_name_kana }}</span>
                                <div>
                                    <span class="font-bold text-lg text-gray-900">{{ $user->full_name }}</span>
                                    <span
                                        class="text-gray-400 font-normal text-sm ml-2">({{ $user->account_name ?? '未設定' }})</span>
                                </div>
                            </div>
                        </div>

                        {{-- クラス：独立した行 --}}
                        <div
                            class="flex flex-col sm:flex-row sm:items-center py-1 border-t border-gray-50 sm:border-none pt-4 sm:pt-0">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">クラス</span>
                            <div class="sm:w-2/3">
                                @if ($user->class)
                                    <x-event.class-tag size="md" :bgColor="$user->class->color()">
                                        {{ $user->class->shortLabel() }}
                                    </x-event.class-tag>
                                @else
                                    <span class="text-gray-300 italic text-sm">未設定</span>
                                @endif
                            </div>
                        </div>

                        {{-- 性別 / 生年月日 --}}
                        <div
                            class="flex flex-col sm:flex-row sm:items-center py-1 border-t border-gray-50 sm:border-none pt-4 sm:pt-0">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">属性</span>
                            <div class="sm:w-2/3 flex gap-8">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-400 font-bold uppercase">Gender</span>
                                    <span class="text-gray-800 font-bold">{{ $user->gender ?? '－' }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-400 font-bold uppercase">Birthday</span>
                                    <span
                                        class="text-gray-800 font-mono font-bold">{{ $user->birthday?->format('Y.m.d') ?? '－' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- 所在地 --}}
                        <div class="flex flex-col sm:flex-row py-1 border-t border-gray-50 sm:border-none pt-4 sm:pt-0">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">所在地</span>
                            <div class="sm:w-2/3 text-gray-800">
                                @if ($user->zip_code)
                                    <p class="text-xs text-user font-mono font-bold mb-1 italic">〒{{ $user->zip_code }}
                                    </p>
                                @endif
                                <p class="leading-relaxed font-medium">
                                    {{ $user->prefecture }}{{ $user->city }}<br>
                                    {{ $user->address_line }}
                                </p>
                                @if (!$user->prefecture && !$user->address_line)
                                    <span class="text-gray-300 italic text-sm">未登録</span>
                                @endif
                            </div>
                        </div>

                        {{-- 電話番号：独立した行 --}}
                        <div
                            class="flex flex-col sm:flex-row sm:items-center py-1 border-t border-gray-50 sm:border-none pt-4 sm:pt-0">
                            <span class="sm:w-1/3 text-gray-500 text-sm font-bold">電話番号</span>
                            <div class="sm:w-2/3">
                                <span class="text-gray-800 font-mono font-bold">{{ $user->phone ?? '未設定' }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <hr class="border-gray-100">

                {{-- 2. 通知設定セクション --}}
                <section>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                        <span class="w-1.5 h-1.5 bg-user rounded-full mr-2"></span>
                        通知設定
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $notificationTypes = [
                                'event_published' => ['label' => '新規イベント公開', 'icon' => 'campaign'],
                                'waitlist_updates' => ['label' => 'キャンセル待ち更新', 'icon' => 'hourglass_top'],
                                'team_invitations' => ['label' => 'チーム招待・回答', 'icon' => 'group_add'],
                            ];
                            $viaLabels = ['mail' => 'メール', 'line' => 'LINE'];
                        @endphp

                        @foreach ($notificationTypes as $type => $info)
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 shadow-sm">
                                <div class="flex items-center mb-3">
                                    <span
                                        class="material-symbols-outlined text-gray-400 text-sm mr-1.5">{{ $info['icon'] }}</span>
                                    <span class="text-sm font-bold text-gray-700">{{ $info['label'] }}</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach (['mail', 'line'] as $via)
                                        @php
                                            $isEnabled = $user->notificationSettings
                                                ->where('type', $type)
                                                ->where('via', $via)
                                                ->where('enabled', true)
                                                ->isNotEmpty();
                                        @endphp
                                        @if ($isEnabled)
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-white text-user border border-user/20 shadow-sm uppercase">
                                                <span class="material-symbols-outlined text-[12px] mr-1">check_circle</span>
                                                {{ $viaLabels[$via] }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-gray-100 text-gray-400 border border-gray-200 uppercase">
                                                <span
                                                    class="material-symbols-outlined text-[12px] mr-1 opacity-50">do_not_disturb_on</span>
                                                {{ $viaLabels[$via] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <hr class="border-gray-100 my-10">

                {{-- 3. セキュリティセクション（管理者側と完全統一） --}}
                <section>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                        <span class="w-1.5 h-1.5 bg-user rounded-full mr-2"></span>
                        セキュリティ
                    </h3>
                    <div class="space-y-3">
                        {{-- メールアドレス --}}
                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 shadow-sm transition-hover hover:bg-white hover:border-user/20 overflow-hidden">
                            <div class="min-w-0 flex-1 mr-3"> {{-- min-w-0 と flex-1 が省略の肝です --}}
                                <span class="block text-[10px] font-black text-gray-400 uppercase">メールアドレス</span>
                                <span class="text-sm font-bold text-gray-700 block truncate" title="{{ $user->email }}">
                                    {{ $user->email }}
                                </span>
                            </div>
                            <button onclick="openEmailModal()"
                                class="flex-shrink-0 text-xs font-bold text-user bg-white border border-user/20 px-4 py-1.5 rounded-lg hover:bg-user hover:text-white transition shadow-sm">
                                変更
                            </button>
                        </div>

                        {{-- パスワード --}}
                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 shadow-sm transition-hover hover:bg-white hover:border-user/20">
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase">パスワード</span>
                                <span
                                    class="text-sm font-bold {{ !empty($user->password) ? 'text-gray-700' : 'text-red-400 italic' }}">
                                    {{ !empty($user->password) ? '••••••••••••' : '未設定' }}
                                </span>
                            </div>
                            <button onclick="location.href='{{ route('user.account.password.edit') }}'"
                                class="text-xs font-bold text-user bg-white border border-user/20 px-4 py-1.5 rounded-lg hover:bg-user hover:text-white transition shadow-sm">
                                {{ !empty($user->password) ? '変更' : '設定' }}
                            </button>
                        </div>

                        {{-- LINE連携（プレイヤー側特殊スタイル） --}}
                        <div
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 shadow-sm transition-hover hover:bg-white hover:border-green-200">
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase">LINE連携</span>
                                <div class="flex items-center mt-0.5">
                                    @php $isLineLinked = $user->socialAccounts->where('provider', 'line')->isNotEmpty(); @endphp
                                    @if ($isLineLinked)
                                        <span class="flex items-center text-green-600 text-xs font-bold">
                                            <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full animate-pulse"></span>連携済み
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs font-bold italic">未連携</span>
                                    @endif
                                </div>
                            </div>
                            @if ($isLineLinked)
                                <form action="{{ route('user.line.disconnect') }}" method="POST"
                                    onsubmit="return confirm('LINE連携を解除しますか？');">
                                    @csrf
                                    <button type="submit"
                                        class="text-xs font-bold text-red-500 bg-white border border-red-200 px-4 py-1.5 rounded-lg hover:bg-red-500 hover:text-white transition shadow-sm">解除</button>
                                </form>
                            @else
                                <a href="{{ route('user.line.login') }}"
                                    class="flex items-center bg-[#06C755] text-white text-[11px] font-bold px-4 py-2 rounded-lg hover:bg-[#05b34c] transition shadow-sm">
                                    LINE連携
                                </a>
                            @endif
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- メール変更モーダル --}}
    @include('user.account._email_modal')

    <script>
        function openEmailModal() {
            document.getElementById('email-modal').classList.remove('hidden');
        }

        function closeEmailModal() {
            document.getElementById('email-modal').classList.add('hidden');
        }
        // submitEmailChangeの実装は管理者側を参考にuser用ルートへ書き換えてください
    </script>
@endsection
