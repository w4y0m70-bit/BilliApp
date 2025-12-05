@extends('user.layouts.app')

@section('title', $event->title)

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-4">{{ $event->title }}</h2>
    <p class="text-sm text-gray-600">
        ［{{ $event->organizer->name ?? '主催者不明' }}］
    </p>

    {{-- イベント概要 --}}
    @if (!empty($event->description))
        <div class="mb-4">
            <p class="text-gray-700 whitespace-pre-line">{{ $event->description }}</p>
        </div>
    @endif

    {{-- 開催情報 --}}
    <div class="text-sm text-gray-700 mb-4 space-y-1">
        <p><strong>開催日時：</strong>{{ format_event_date($event->event_date) }} {{ $event->event_date->format('H:i') }}</p>
        <p><strong>エントリー締切：</strong>{{ $event->entry_deadline->format('Y/m/d H:i') }}</p>
        <p><strong>参加人数：</strong>
            {{ $event->entry_count }}／{{ $event->max_participants }}人
            （{{ $event->allow_waitlist ? $event->waitlist_count : '－' }}）
        </p>
    </div>

    @php
        $currentUser = Auth::user() ?? \App\Models\User::first();
        $userEntry = $event->userEntries()
            ->where('user_id', $currentUser->id)
            ->where('status', '!=', 'cancelled')
            ->latest('created_at')
            ->first();

        $status = $userEntry ? $userEntry->status : null;
        $isFull = $event->entry_count >= $event->max_participants;
        $waitlistCount = $event->userEntries()->where('status', 'waitlist')->count();
        $userWaitlistUntil = $userEntry?->waitlist_until?->format('Y-m-d\TH:i') ?? $event->entry_deadline->format('Y-m-d\TH:i');
        $entryRoute = $userEntry
            ? route('user.entries.update', ['event' => $event->id, 'entry' => $userEntry->id])
            : route('user.entries.entry', ['event' => $event->id]);
        $method = $userEntry ? 'PATCH' : 'POST';
        $deadlineValue = old(
        'waitlist_until',
        $userEntry?->waitlist_until?->format('Y-m-d\TH:i') ?? $event->entry_deadline->format('Y-m-d\TH:i')
    );
    @endphp

    <div class="text-center mt-6 space-y-3">
        {{-- エントリーフォーム --}}
        <form action="{{ $entryRoute }}" method="POST" onsubmit="return confirmEntryUpdate();">
            @csrf
            @if($userEntry)
                @method('PATCH')
            @endif

            @if($isFull && $event->allow_waitlist)
                <div class="mb-4">
                    <div class="flex items-center gap-3 justify-center">
                        <label class="inline-flex items-center gap-2 whitespace-nowrap">
                            <input type="checkbox" name="useDeadline" id="useDeadlineCheckbox"
                                {{ optional($userEntry)->waitlist_until ? 'checked' : '' }}>
                            キャンセル待ち期限を設定する
                        </label>

                        <input 
                            type="datetime-local" 
                            name="waitlist_until" 
                            id="waitlistUntil"
                            x-bind:disabled="!useDeadline"
                            value="{{ old('waitlist_until', optional($userEntry)->waitlist_until?->format('Y-m-d\TH:i') ?? $event->entry_deadline->format('Y-m-d\TH:i')) }}"
                            class="border rounded px-3 py-2"
                            {{ optional($userEntry)->waitlist_until ? '' : 'disabled' }}
                        >
                    </div>
                </div>
            @endif

            <button type="submit" class="bg-user text-white px-4 py-2 rounded hover:bg-user-dark transition">
                {{ $userEntry ? '更新する' : 'このイベントにエントリーする' }}
            </button>
        </form>

        {{-- キャンセルボタン（エントリー済みのみ表示） --}}
        @if($userEntry)
            <form 
                action="{{ route('user.entries.cancel', ['event' => $event->id, 'entryId' => $userEntry->id]) }}" 
                method="POST"
                onsubmit="return confirmCancel();"
            >
                @csrf
                @method('PATCH')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition mt-2">
                    エントリーをキャンセルする
                </button>
            </form>
        @endif

        <a href="{{ route('user.events.index') }}" class="inline-block bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">
            一覧に戻る
        </a>
    </div>
</div>

<script>
function confirmCancel() {
    let message = 'このイベントのエントリーをキャンセルしますか？';
    @if($waitlistCount > 0)
        message += '\n現在キャンセル待ちの人がいます。再度エントリーするとキャンセル待ちの最後に登録されます。';
    @endif
    return confirm(message);
}

function confirmEntryUpdate() {
    let message = '';
    @if($isFull)
        message = '満員のため、キャンセル待ちに登録されます。';
    @else
        message = '{{ $userEntry ? "キャンセル待ち期限を更新します。" : "このイベントにエントリーします。" }}';
    @endif
    return confirm(message);
}

document.addEventListener("DOMContentLoaded", function() {
    const checkbox = document.getElementById('useDeadlineCheckbox');
    const input = document.getElementById('waitlistUntil');

    if (!checkbox || !input) return;

    checkbox.addEventListener('change', function() {
        if (checkbox.checked) {
            input.disabled = false;
            input.classList.remove("bg-gray-200", "text-gray-500", "cursor-not-allowed");
        } else {
            input.disabled = true;
            input.classList.add("bg-gray-200", "text-gray-500", "cursor-not-allowed");
        }
    });
});
</script>
@endsection
