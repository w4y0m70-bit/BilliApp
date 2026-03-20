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
                        @if(!empty($participant->user_answer))
                            <button @click.stop="openMessage = !openMessage" class="hover:text-indigo-400 transition-colors">
                                <span class="material-symbols-outlined text-base">chat_bubble</span>
                            </button>
                        @endif
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
                        
                        {{-- ステータスアイコン（さらに小さく控えめに） --}}
                        <div class="flex-shrink-0 flex items-center justify-center w-3.5">
                            @if($member->invite_status === 'approved')
                                <span class="material-symbols-outlined text-green-500 text-sm" title="承認済み">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-amber-400 text-sm animate-pulse" title="承認待ち">pending</span>
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

                        {{-- クラス（右端で縦を揃える） --}}
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

            {{-- 管理者メモ・オーバーレイ --}}
            <div x-show="openMessage" x-cloak 
                 class="absolute inset-0 z-20 bg-gray-900/95 backdrop-blur-sm text-white p-3 flex flex-col justify-center">
                <div class="flex justify-between items-center mb-1 border-b border-gray-700 pb-1">
                    <span class="text-[9px] font-bold tracking-widest text-gray-400 uppercase">Admin Memo</span>
                    <button @click="openMessage = false" class="text-xs p-1 hover:bg-white/20 rounded">×</button>
                </div>
                <div class="text-[11px] leading-snug overflow-y-auto max-h-full py-1">
                    {{ $participant->user_answer }}
                </div>
            </div>
        </div>
    @endforeach
</div>