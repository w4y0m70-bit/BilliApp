@props(['event'])

@php
    $currentUser = Auth::user();

    // エントリー情報の取得
    $userEntry = $event
        ->userEntries()
        ->where(function ($query) use ($currentUser) {
            $query
                ->where('representative_user_id', $currentUser->id)
                ->orWhereHas('members', fn($q) => $q->where('user_id', $currentUser->id));
        })
        ->where('status', '!=', 'cancelled')
        ->latest('created_at')
        ->first();

    $status = $userEntry->status ?? null;

    // 招待判定
    $isInvited = $event
        ->userEntries()
        ->whereIn('status', ['pending', 'waitlist'])
        ->whereHas('members', fn($q) => $q->where('user_id', $currentUser->id)->where('invite_status', 'pending'))
        ->exists();

    $isDeadlinePast = $event->entry_deadline->isPast();
@endphp

<div
    class="group bg-white shadow-sm hover:shadow-md rounded-xl border border-gray-100 overflow-hidden transition-all duration-200 flex flex-col h-full relative">

    {{-- 1. 主催者エリア（クリックで詳細が開く） --}}
    <div class="px-3 py-1 bg-gray-50/80 border-b border-gray-100 flex justify-between items-center relative z-10"
        x-data="{ showOrganizer: false }">
        <button @click.stop="showOrganizer = !showOrganizer" type="button"
            class="text-[12px] font-bold text-gray-400 hover:text-user flex items-center transition truncate max-w-[70%]">
            <span class="truncate"> {{ $event->organizer->name ?? '主催者不明' }}</span>
            <span class="material-symbols-outlined text-xs ml-0.5 transition-transform"
                :class="showOrganizer ? 'rotate-180' : ''">expand_more</span>
        </button>

        <div class="flex gap-1">
            @foreach ($event->requiredGroups as $group)
                <span
                    class="text-[9px] px-1.5 py-0.5 rounded-md bg-blue-50 text-blue-600 font-bold border border-blue-100 leading-none">
                    {{ $group->name }}
                </span>
            @endforeach
        </div>

        {{-- 主催者パネル --}}
        <div x-show="showOrganizer" @click.away="showOrganizer = false" x-transition x-cloak
            class="absolute top-full left-2 z-30 p-2 bg-white rounded-lg border border-gray-200 text-[10px] shadow-xl w-48">
            <p class="text-gray-400 mb-1 border-b pb-1 font-bold text-[9px]">主催者情報</p>
            <p class="text-gray-700 leading-tight mb-2">
                {{ $event->organizer->prefecture }}{{ $event->organizer->city }}{{ $event->organizer->address_line }}
            </p>
            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event->organizer->prefecture . $event->organizer->city . $event->organizer->address_line) }}"
                target="_blank"
                class="text-blue-600 font-bold flex items-center justify-center bg-blue-50 py-1 rounded hover:bg-blue-100 transition">
                <span class="material-symbols-outlined text-xs mr-1">map</span>Googleマップ
            </a>
        </div>
    </div>

    {{-- 2. コンテンツエリア --}}
    <div class="p-3 flex-1 flex flex-col">
        <h3 class="text-xl font-black mb-2 text-user leading-snug group-hover:text-user transition-colors line-clamp-2">
            <a href="{{ route('user.events.show', $event->id) }}" class="hover:underline">
                {{ $event->title }}
            </a>
            @if ($event->max_team_size > 1)
                @php
                    $teamType = $event->getTeamType();
                @endphp

                <span class="inline-flex items-center ml-1">
                    <span
                        class="text-[9px] px-1.5 py-0.5 rounded {{ $teamType->colorClass() }} uppercase tracking-tighter align-middle">
                        {{ $teamType->label() }}
                        @if ($event->max_team_size > 5)
                            （{{ $event->max_team_size }}名）
                        @endif
                    </span>
                </span>
            @endif
            <x-help help-key="user.events.show" class="ml-2" />
        </h3>

        {{-- クラス表示 --}}
        <div class="mb-3">
            <div class="flex flex-wrap gap-1 items-center">
                <span class="text-[12px] font-bold text-gray-400 mr-1 whitespace-nowrap">クラス:</span>
                @forelse($event->eventClasses as $class)
                    @php
                        $classEnum = \App\Enums\PlayerClass::tryFrom($class->class_name);
                    @endphp
                    @if ($classEnum)
                        <x-event.class-tag size="xs" :bgColor="$classEnum->color()">
                            {{ $classEnum->shortLabel() }}
                        </x-event.class-tag>
                    @else
                        <span
                            class="text-[9px] px-1 border rounded bg-gray-100 text-gray-500">{{ $class->class_name }}</span>
                    @endif
                @empty
                    <span class="text-gray-300 text-[10px]">ー</span>
                @endforelse
                <x-help help-key="user.events.class" class="ml-2" />
            </div>
        </div>

        {{-- 日時情報（日本語化・コンパクト） --}}
        <div class="text-[12px] space-y-1 mt-auto border-t border-gray-50 pt-2">
            <div class="flex justify-between items-center text-gray-700">
                <span class="text-gray-400 font-bold text-[12px]">開催日時</span>
                <span class="font-black text-[14px] text-user">
                    {{ format_event_date($event->event_date) }}
                    <span class="text-gray-400 font-normal text-[14px]">{{ $event->event_date->format('H:i') }}</span>
                </span>
            </div>
            <div class="flex justify-between items-center {{ $isDeadlinePast ? 'text-gray-300' : 'text-gray-700' }}">
                <span class="text-gray-400 font-bold text-[12px]">エントリー締切</span>
                <span class="font-nomal text-[14px]">
                    {{ format_event_date($event->entry_deadline) }}
                    <span
                        class="text-gray-400 font-normal text-[14px]">{{ $event->entry_deadline->format('H:i') }}</span>
                </span>
            </div>
            {{-- キャンセル待ち期限（条件付き表示） --}}
            @if ($status === 'waitlist' && isset($userEntry->waitlist_until))
                <div class="flex justify-between items-center text-orange-600 ">
                    <span class="text-orange-400 font-bold text-[12px]">キャンセル待ち期限</span>
                    <span class="font-bold text-[14px]">
                        {{ format_event_date($userEntry->waitlist_until) }}
                        <span
                            class="text-orange-400 font-normal text-[14px]">{{ $userEntry->waitlist_until->format('H:i') }}</span>
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- 3. ステータス表示（ラベル） --}}
    <div class="px-3 pb-3">
        <div class="flex items-center justify-between text-[14px] border-t border-gray-50 pt-2">
            <div>
                <x-event.entry-status :event="$event" :href="route('user.events.participants', $event->id)" class="font-bold text-gray-400" />
                <x-help help-key="user.events.entry-status" class="ml-2" />
            </div>
            <div class="font-black">
                @if ($isInvited)
                    <span class="text-blue-600 animate-pulse">● 招待あり</span>
                @elseif ($status === 'entry')
                    <span class="text-user">● エントリー中</span>
                @elseif ($status === 'waitlist')
                    <span class="text-orange-500">● キャンセル待ち中（{{ $userEntry->waitlist_position }}）</span>
                @elseif ($isDeadlinePast)
                    <span class="text-gray-300">受付終了</span>
                @else
                    <span class="text-gray-400">未エントリー</span>
                @endif
            </div>
        </div>
    </div>
</div>
