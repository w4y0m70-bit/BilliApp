@props([
    'event',
    'participants', 
    'mode' => 'user', 
    'maxEntries' => 0,
])

@php
    $isAdmin = in_array($mode, ['admin', 'master']);
@endphp

<div class="flex flex-col gap-1">
    @foreach ($participants as $participant)
        <div x-data="{ openMessage: false, showAdmin: false }" 
             @mouseenter="showAdmin = true" 
             @mouseleave="showAdmin = false"
             class="relative flex bg-white border {{ $participant->status === 'waitlist' ? 'border-orange-200 bg-orange-50/50' : 'border-gray-200 shadow-sm' }} rounded-md overflow-hidden">
            
            {{-- 1. 左端：No. & 操作エリア（WL-10でも折り返さない幅 w-12） --}}
            <div class="relative flex-shrink-0 w-12 flex flex-col items-center justify-center border-r {{ $participant->status === 'waitlist' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-400' }}">
                {{-- 通常時：No./WL表示 --}}
                <div class="flex items-center justify-center font-black text-xs">
                    @php
                        $limit = (int)$maxEntries;
                        $order = (int)$participant->order;
                    @endphp

                    @if($order > $limit)
                        {{-- orderが定員(2)より大きければ、一律で WL表示 --}}
                        WL{{ $order - $limit }}
                    @else
                        {{-- 定員内ならそのまま 1, 2 --}}
                        {{ $order }}
                    @endif
                </div>

                {{-- ホバー時：操作ボタン --}}
                @if($isAdmin)
                    <div x-show="showAdmin" x-cloak class="absolute inset-0 flex items-center justify-center bg-gray-800 text-white gap-2">
                        <button onclick="window.globalCancelEntry({{ $participant->event_id }}, {{ $participant->id }})" class="hover:text-red-400 transition-colors">
                            <span class="material-symbols-outlined text-base">delete</span>
                        </button>
                        <!-- @if(!empty($participant->user_answer))
                            <button @click.stop="openMessage = !openMessage" class="hover:text-indigo-400 transition-colors">
                                <span class="material-symbols-outlined text-base">chat_bubble</span>
                            </button>
                        @endif -->
                    </div>
                @endif
            </div>

            {{-- 2. 右側：コンテンツエリア --}}
            <div class="flex-grow flex flex-col divide-y divide-gray-100 min-w-0">
                
                {{-- チーム名行（天地を詰めつつ視認性確保） --}}
                @if($event->max_team_size >= 2 && $participant->team_name)
                    <div class="bg-gray-50/50 px-2 py-0.5 flex items-center">
                        <span class="text-[11px] font-black text-gray-600 truncate leading-tight">
                            <span class="text-[8px] font-normal mr-1 text-gray-400 uppercase">Team:</span>{{ $participant->team_name }}
                        </span>
                    </div>
                @endif

                {{-- メンバー行 --}}
                @foreach($participant->members as $member)
                    <div class="flex items-center h-8 px-2 gap-1.5 hover:bg-gray-50/40 transition-colors">
                        
                        {{-- ステータス & メッセージアイコン領域（幅を固定して位置を揃える） --}}
                        <div class="flex-shrink-0 flex items-center justify-center w-4">
                            @if($member->invite_status !== 'approved')
                                {{-- 承認待ちのみ表示 --}}
                                <span class="material-symbols-outlined text-amber-400 text-[16px] animate-pulse" title="承認待ち">pending</span>
                            @elseif(!empty($participant->user_answer))
                                {{-- 承認済み かつ メッセージがある場合のみチャットアイコンを表示 --}}
                                <button @click.stop="openMessage = !openMessage" class="flex items-center justify-center text-indigo-500 hover:text-indigo-700 transition-colors" title="メッセージあり">
                                    <span class="material-symbols-outlined text-[16px]">chat_bubble</span>
                                </button>
                            @else
                                {{-- メッセージなしの場合は空白（名前の位置を揃えるためのプレースホルダー） --}}
                                <div class="w-4"></div>
                            @endif
                        </div>

                        {{-- 氏名（性別色分け） --}}
                        <div class="flex-grow min-w-0 flex items-baseline gap-1">
                            <span class="text-sm font-bold truncate {{ $member->gender === '女性' ? 'text-pink-600' : 'text-gray-800' }}">
                                {{ $isAdmin ? $member->full_name : ($member->user->account_name ?? $member->full_name) }}
                            </span>
                            @if($isAdmin && $member->user)
                                <span class="text-[10px] text-gray-400 font-normal truncate">({{ $member->user->account_name }})</span>
                            @endif
                            @if($participant->event->max_team_size > 1 && 
                                $member->user_id &&
                                $member->user_id === $participant->representative_user_id)
                                <span class="text-[8px] text-indigo-500 font-bold px-0.5 border border-indigo-200 rounded-[2px] leading-none flex-shrink-0">代表</span>
                            @endif
                        </div>

                        {{-- クラス --}}
                        <div class="flex-shrink-0 w-8 flex justify-end">
                            <span class="text-[10px] font-black text-gray-500 bg-gray-100 px-1 py-0.5 rounded-sm min-w-[20px] text-center leading-none">
                                @php
                                    $val = $member->class;
                                    if ($val instanceof \App\Enums\PlayerClass) echo $val->shortLabel();
                                    elseif ($enum = \App\Enums\PlayerClass::tryFrom($val)) echo $enum->shortLabel();
                                    else echo $val ?? '-';
                                @endphp
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 管理者メモ・ポップアップ（モーダル） --}}
            <template x-teleport="body">
                <div x-show="openMessage" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
                    x-cloak>
                    
                    {{-- モーダル本体 --}}
                    <div @click.away="openMessage = false" 
                        class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-lg shadow-xl overflow-hidden flex flex-col items-stretch"> {{-- items-stretch を追加 --}}
                        
                        <div class="flex justify-between items-center px-4 py-3 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700 w-full">
                            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                {{-- 代表者の名前を表示 --}}
                                {{ $participant->members->first()->full_name ?? '参加者' }} からのメッセージ
                            </h4>
                            <button @click="openMessage = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-xl">×</button>
                        </div>

                        {{-- メッセージエリア：text-left を徹底 --}}
                        <div class="p-5 w-full text-left"> {{-- text-left をここにも追加 --}}
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed w-full text-left">{{ trim($participant->user_answer) }}</p>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-right w-full">
                            <button @click="openMessage = false" 
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold rounded hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                                閉じる
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    @endforeach
</div>