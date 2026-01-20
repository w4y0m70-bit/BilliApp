@extends('user.layouts.app')

@section('title', '公開イベント一覧')

@section('content')
<div class="px-4">
    <h2 class="text-2xl font-bold mb-4">公開中のイベント
    <span help-key="user.events.index" class="inline-block mb-4">
        <x-help help-key="user.events.index" />
    </span>
    </h2>

    @if($events->isEmpty())
        <p>公開中のイベントはありません。</p>
    @else
    <div 
        class="grid gap-4"
        style="
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        "
    >
        @foreach ($events as $event)
            @php
                $currentUser = Auth::user();
                $userEntry = $event->userEntries()
                    ->where('user_id', $currentUser->id)
                    ->where('status', '!=', 'cancelled')
                    ->latest('created_at')
                    ->first();

                $status = $userEntry->status ?? null;
                $isFull = $event->entry_count >= $event->max_participants;
            @endphp

            {{-- カード部分 --}}
            <div class="block bg-white shadow rounded-xl p-4 border hover:shadow-lg transition">

                <p class="text-sm font-bold text-gray-600">
                    ［{{ $event->organizer->name ?? '主催者不明' }}］
                </p>

                {{-- タイトルをリンクに --}}
                <h3 class="text-2xl font-black mb-1 text-user">
                    <a href="{{ route('user.events.show', $event->id) }}" class="hover:underline">
                        {{ $event->title }}
                    </a>
                    <x-help help-key="user.events.show" />
                </h3>

                <p class="text-sm text-gray-700">
                    <strong>開催日時：</strong><span class="text-lg font-bold">
                    {{ format_event_date($event->event_date) }}
                    {{ $event->event_date->format('H:i') }}
                </span></p>

                <p class="text-sm text-gray-700 mt-1">
                    <strong>エントリー締切：</strong>
                    {{ format_event_date($event->entry_deadline) }}
                    {{ $event->entry_deadline->format('H:i') }}
                </p>

                <div class="flex items-center">
                    <p class="text-sm text-gray-700 mt-1">
                        <strong>キャンセル待ち期限：</strong>
                        @if ($status === 'waitlist' && $userEntry->waitlist_until)
                            {{ format_event_date($userEntry->waitlist_until) }}
                            {{ $userEntry->waitlist_until->format('H:i') }}
                        @else
                            —
                        @endif
                    </p>
                    <x-help help-key="user.events.waitlist_until" />
                </div>

                {{-- ★ 修正箇所：参加人数の数字をリンクにする --}}
                <div class="flex items-center mb-1">
                <p class="text-sm text-gray-700 mt-1">
                    <strong>参加人数：</strong>
                    <a href="{{ route('user.events.participants', $event->id) }}" class="text-blue-600 hover:underline font-bold">
                        {{ $event->entry_count }}
                        ／{{ $event->max_participants }}人
                        （
                        @if($event->allow_waitlist)
                        WL：{{ $event->waitlist_count }}
                        @else
                        ✕
                        @endif
                        ）
                    </a>
                </p>
                <x-help help-key="user.events.participants" />
                </div>

                {{-- 状態バッジ --}}
                <div class="mt-3">
                    @if ($status === 'entry')
                            <span class="inline-block bg-user text-white text-sm px-3 py-1 rounded transition">
                                エントリー中
                            </span>
                        @elseif ($status === 'waitlist')
                            <span class="inline-block bg-orange-500 text-white text-sm px-3 py-1 rounded transition">
                                キャンセル待ち（{{ $userEntry->waitlist_position ?? '' }}番目）
                            </span>
                        @else
                            <span class="inline-block bg-gray-400 text-white text-sm px-3 py-1 rounded transition">
                                @if($isFull && !$event->allow_waitlist)
                                満員
                                @else
                                未エントリー
                                @endif
                            </span>
                        @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>

{{-- 過去にエントリーしたイベント --}}
@if(isset($pastEntries) && $pastEntries->count() > 0)
<div class="bg-gray-50 shadow rounded-lg p-6 mt-8">
    <h2 class="text-xl font-bold mb-4">過去にエントリーしたイベント</h2>
    @foreach ($pastEntries as $entry)
        <div class="p-3 border-b last:border-0">
            <strong>{{ $entry->event->title }}</strong>
            <span class="text-sm text-gray-600">{{ $entry->event->event_date->format('Y/m/d H:i') }}</span>
        </div>
    @endforeach
</div>
@endif

@endsection
