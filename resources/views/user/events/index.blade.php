@extends('user.layouts.app')

@section('title', '公開イベント一覧')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-4">公開中のイベント</h2>

    @forelse ($events as $event)
        @php
    $currentUser = Auth::user() ?? \App\Models\User::first();

    // 最新のエントリーを取得（キャンセル済みは除外）
    $userEntry = $event->userEntries()
        ->where('user_id', $currentUser->id)
        ->where('status', '!=', 'cancelled')
        ->latest('created_at')
        ->first();

    $status = $userEntry ? $userEntry->status : null;
@endphp


        <a href="{{ route('user.events.show', $event->id) }}" class="block p-4 border mb-2 rounded hover:bg-gray-50 transition">
            <h3 class="text-lg font-bold">{{ $event->title }}</h3>
            <p class="text-sm text-gray-700">
                <strong>開催日時：</strong>{{ $event->event_date->format('Y/m/d H:i') }}
            </p>
            <p class="text-sm text-gray-700">
                <strong>エントリー締切：</strong>{{ $event->entry_deadline->format('Y/m/d H:i') }}
            </p>
            <p class="text-sm text-gray-700">
                <strong>参加人数：</strong>
                {{ $event->entry_count }}／{{ $event->max_participants }}人
                （{{ $event->allow_waitlist ? $event->waitlist_count : '－' }}）
            </p>

            {{-- 状態表示 --}}
            @if ($status === 'entry')
                <span class="inline-block bg-user text-white text-sm px-2 py-1 rounded">エントリー中</span>
            @elseif ($status === 'waitlist')
                <span class="inline-block bg-yellow-500 text-white text-sm px-2 py-1 rounded">キャンセル待ち中</span>
            @endif
        </a>
    @empty
        <p>公開中のイベントはありません。</p>
    @endforelse
</div>

{{-- 🔹 過去のイベント一覧 --}}
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
