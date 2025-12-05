@extends('user.layouts.app')

@section('title', '公開イベント一覧')

@section('content')
<div class="px-4 py-6">
    <h2 class="text-2xl font-bold mb-4">公開中のイベント</h2>

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
            @endphp

            <a href="{{ route('user.events.show', $event->id) }}"
                class="block bg-white shadow rounded-xl p-4 border hover:shadow-lg transition"
            >
                <p class="text-sm text-gray-600 mb-1">
                    ［{{ $event->organizer->name ?? '主催者不明' }}］
                </p>

                <h3 class="text-lg font-bold mb-2">{{ $event->title }}</h3>

                <p class="text-sm text-gray-700">
                    <strong>開催日時：</strong>
                    {{ format_event_date($event->event_date) }}
                    {{ $event->event_date->format('H:i') }}
                </p>

                <p class="text-sm text-gray-700 mt-1">
                    <strong>エントリー締切：</strong>
                    {{ format_event_date($event->entry_deadline) }}
                    {{ $event->entry_deadline->format('H:i') }}
                </p>

                <p class="text-sm text-gray-700 mt-1">
                    <strong>キャンセル待ち期限：</strong>
                    @if ($status === 'waitlist' && $userEntry->waitlist_until)
                        {{ format_event_date($userEntry->waitlist_until) }}
                        {{ $userEntry->waitlist_until->format('H:i') }}
                    @else
                        —
                    @endif
                </p>

                <p class="text-sm text-gray-700 mt-1">
                    <strong>参加人数：</strong>
                    {{ $event->entry_count }}／{{ $event->max_participants }}
                    （{{ $event->allow_waitlist ? $event->waitlist_count : '－' }}）
                </p>

                {{-- 状態バッジ --}}
                <div class="mt-3">
                    @if ($status === 'entry')
                        <span class="inline-block bg-user text-white text-sm px-3 py-1 rounded">
                            エントリー中
                        </span>
                    @elseif ($status === 'waitlist')
                        <span class="inline-block bg-orange-500 text-white text-sm px-3 py-1 rounded">
                            キャンセル待ち（{{ $userEntry->waitlist_position ?? '' }}番目）
                        </span>
                    @else
                        <span class="inline-block bg-gray-400 text-white text-sm px-3 py-1 rounded">
                            未エントリー
                        </span>
                    @endif
                </div>
            </a>
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
            <p class="text-sm text-gray-600">{{ $entry->event->event_date->format('Y/m/d H:i') }}</p>
        </div>
    @endforeach
</div>
@endif

@endsection
