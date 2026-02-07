@props([
    'ticket', 
    'tab' => 'ready', 
    'count' => 1
])

@php
    $conf = [
        'active' => ['label' => 'IN USE', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
        'ready'  => ['label' => 'READY',  'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
        'used'   => ['label' => 'USED',   'bg' => 'bg-gray-100', 'text' => 'text-gray-400'],
    ][$tab];

    $isUrgent = ($tab === 'ready' && $ticket->isUrgent());

    // プラン名に基づいて「完全なクラス名」を生成
    $planName = $ticket->plan->display_name;
    if ($planName === 'POCKET') {
        $textColor = 'text-ticket_a';
        $borderColor = 'border-ticket_a';
        $bgColor = 'bg-ticket_a';
    } elseif ($planName === 'RACK') {
        $textColor = 'text-ticket_b';
        $borderColor = 'border-ticket_b';
        $bgColor = 'bg-ticket_b';
    } elseif ($planName === 'TABLE') {
        $textColor = 'text-ticket_c';
        $borderColor = 'border-ticket_c';
        $bgColor = 'bg-ticket_c';
    } else {
        $textColor = 'text-gray-500';
        $borderColor = 'border-gray-500';
        $bgColor = 'bg-gray-500';
    }
@endphp

<div class="relative flex flex-col bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden {{ $tab === 'used' ? 'opacity-70' : '' }} transition-transform active:scale-95">
    
    {{-- 枚数グループ --}}
    @if($tab === 'ready' && $count > 1)
        <div class="absolute top-0 right-0 z-10">
            <div class="bg-gray-500 text-white text-[16px] font-normal px-2 py-0.5 rounded-bl-lg">
                ×{{ $count }}
            </div>
        </div>
    @endif

    {{-- メインエリア --}}
    <div class="p-3 flex-grow flex flex-col justify-between min-h-[110px]">
        <div>
            {{-- チケット名 & アイコン風定員 --}}
            <div class="flex items-center gap-1.5 mb-2">
                {{-- プラン名 --}}
                <span class="text-m font-black tracking-tighter {{ $textColor }}">
                    {{ $planName }}
                </span>
                {{-- 定員アイコン --}}
                <span class="border-2 border-solid {{ $borderColor }} {{ $textColor }} text-m font-black px-1.5 py-0.5 rounded-md leading-none bg-transparent">
                    {{ $ticket->plan->max_capacity }}
                </span>
            </div>

            {{-- イベント名 --}}
            <div class="text-sm font-bold text-gray-800 leading-tight line-clamp-2 min-h-[2.5em]">
                @if($tab !== 'ready' && $ticket->event)
                    {{ $ticket->event->title }}
                @else
                    <span class="text-gray-300 font-normal">（未使用）</span>
                @endif
            </div>
        </div>

        {{-- 下部：日付 --}}
        <div class="mt-2 pt-2 border-t border-dotted border-gray-200">
            <div class="text-[10px] {{ $isUrgent ? 'text-red-600 font-bold' : 'text-gray-400' }}">
                {{ $tab === 'used' ? '開催日' : '有効期限' }}
            </div>
            <div class="text-lg font-bold {{ $isUrgent ? 'text-red-600' : 'text-gray-700' }}">
                @if($isUrgent)<span class="animate-ping inline-block w-1 h-1 bg-red-600 rounded-full mr-1"></span>@endif
                {{ ($tab === 'used' && $ticket->event) ? $ticket->event->event_date->format('Y/m/d') : $ticket->expired_at->format('Y/m/d') }}
            </div>
        </div>
    </div>

    {{-- 下部：ボタンエリア --}}
    <div class="{{ $conf['bg'] }} py-2 px-3 border-t border-dashed border-gray-300 flex flex-col items-center justify-center">
        @if($tab === 'ready')
            <a href="{{ route('admin.events.create', ['ticket_id' => $ticket->id]) }}" 
               class="w-full text-center {{ $bgColor }} text-white text-xs py-1.5 rounded-lg font-black hover:opacity-90 shadow-sm transition-opacity">
                使う
            </a>
        @else
            <span class="text-[10px] font-black {{ $conf['text'] }} tracking-widest uppercase italic">
                - {{ $conf['label'] }} -
            </span>
        @endif
    </div>
</div>