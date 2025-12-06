@extends('user.layouts.app')

@section('title', $event->title)

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <p class="text-sm text-gray-600">
        ［{{ $event->organizer->name ?? '主催者不明' }}］
    </p>
    <h2 class="text-2xl font-bold mb-4">{{ $event->title }}</h2>

    @php
        $currentUser = Auth::user() ?? \App\Models\User::first();
        $userEntry = $event->userEntries()
            ->where('user_id', $currentUser->id)
            ->where('status', '!=', 'cancelled')
            ->latest('created_at')
            ->first();

        $isFull = $event->entry_count >= $event->max_participants;
        $canWaitlist = $event->allow_waitlist;
        $canEntry = !$isFull || ($isFull && $canWaitlist);

        $entryRoute = $userEntry
            ? route('user.entries.update', ['event' => $event->id, 'entry' => $userEntry->id])
            : route('user.entries.entry', ['event' => $event->id]);

        $deadlineValue = old(
            'waitlist_until',
            $userEntry?->waitlist_until?->format('Y-m-d\TH:i') ?? $event->entry_deadline->format('Y-m-d\TH:i')
        );
        $isBeforeDeadline = optional($userEntry)?->waitlist_until?->lt($event->entry_deadline);
    @endphp

    
    
    
    {{-- 全体フォーム（右側更新／エントリー） --}}
    <form action="{{ $entryRoute }}" method="POST" onsubmit="return confirmEntryUpdate();" class="space-y-4">
        @csrf
        @if($userEntry)
        @method('PATCH')
        @endif
        
        {{-- 開催情報・キャンセル待ち期限入力 --}}
        <div class="text-sm text-gray-700 mb-4 space-y-1">
            <p><strong>開催日時：</strong>{{ format_event_date($event->event_date) }} {{ $event->event_date->format('H:i') }}</p>
            <p><strong>エントリー締切：</strong>{{ format_event_date($event->entry_deadline) }} {{ $event->entry_deadline->format('H:i') }}</p>
            
            <label class="inline-block text-sm text-gray-700 mb-1">キャンセル待ち期限：</label>
            <input type="datetime-local"
                name="waitlist_until"
                value="{{ $deadlineValue }}"
                class="border rounded px-3 py-2 mb-3 {{ $isBeforeDeadline ? 'text-user' : '' }}"
                style="width: calc(2/3 * 18rem);">
        </div>
        
        {{-- イベント概要 --}}
        @if(!empty($event->description))
        <div class="mb-4">
            <p class="text-gray-700 whitespace-pre-line">{{ $event->description }}</p>
        </div>
        @endif
        
        {{-- 右側ボタン群 --}}
        <div class="flex justify-end items-center gap-3">
            <button type="submit"
            class="px-4 py-2 rounded text-white transition
            {{ $canEntry ? 'bg-user hover:bg-user-dark' : 'bg-gray-400 cursor-not-allowed' }}"
            {{ $canEntry ? '' : 'disabled' }}>
            @if(!$canEntry)
            満員のためエントリー不可
            @elseif($userEntry)
            キャンセル待ち期限を更新
            @else
            このイベントにエントリー
            @endif
        </button>
        
        <a href="{{ route('user.events.index') }}" class="inline-block bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">
            戻る
        </a>
    </div>
</form>

{{-- キャンセルボタン（左） --}}
@if($userEntry)
    <form action="{{ route('user.entries.cancel', ['event' => $event->id, 'entryId' => $userEntry->id]) }}" method="POST" onsubmit="return confirmCancel();">
        @csrf
        @method('PATCH')
        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
            エントリーをキャンセル
        </button>
    </form>
@endif
</div>

<script>
    function confirmCancel() {
        let message = 'このイベントのエントリーをキャンセルしますか？';
        @if($event->userEntries()->where('status','waitlist')->count() > 0)
        message += '\n現在キャンセル待ちの人がいます。再度エントリーするとキャンセル待ちの最後に登録されます。';
        @endif
    return confirm(message);
}

function confirmEntryUpdate() {
    @if($userEntry)
        return confirm('キャンセル待ち期限を更新します。');
    @elseif($isFull)
        return confirm('満員のため、キャンセル待ちに登録されます。');
    @else
        return confirm('このイベントにエントリーします。');
    @endif
}
</script>
@endsection
