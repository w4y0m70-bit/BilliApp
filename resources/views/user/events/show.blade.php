@extends('user.layouts.app')

@section('title', $event->title)

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-4">{{ $event->title }}</h2>

    {{-- イベント概要 --}}
    @if (!empty($event->description))
        <div class="mb-4">
            <h3 class="text-md font-semibold mb-1 text-gray-700">イベント内容</h3>
            <p class="text-gray-700 whitespace-pre-line">{{ $event->description }}</p>
        </div>
    @endif

    {{-- 開催情報 --}}
    <div class="text-sm text-gray-700 mb-4 space-y-1">
        <p><strong>開催日時：</strong>{{ $event->event_date->format('Y/m/d H:i') }}</p>
        <p><strong>エントリー締切：</strong>{{ $event->entry_deadline->format('Y/m/d H:i') }}</p>
        <p><strong>参加人数：</strong>
            {{ $event->entry_count }}／{{ $event->max_participants }}人
            （{{ $event->allow_waitlist ? $event->waitlist_count : '－' }}）
        </p>
    </div>

    @php
    $currentUser = Auth::user() ?? \App\Models\User::first();

    // 最新のエントリーを取得（キャンセル済みは除外）
    $userEntry = $event->userEntries()
        ->where('user_id', $currentUser->id)
        ->where('status', '!=', 'cancelled')
        ->latest('created_at')
        ->first();

    $status = $userEntry ? $userEntry->status : null;

    // キャンセル待ち人数
    $waitlistCount = $event->userEntries()->where('status', 'waitlist')->count();
    $isFull = $event->entry_count >= $event->max_participants;
@endphp

<div class="text-center mt-6 space-y-3">
    @if (in_array($status, ['entry', 'waitlist']))
        {{-- エントリー中 or キャンセル待ち中 → キャンセルボタン --}}
        <form 
            action="{{ route('user.entries.cancel', ['event' => $event->id, 'entryId' => $userEntry->id]) }}" 
            method="POST"
            onsubmit="return confirmCancel();"
        >
            @csrf
            @method('PATCH')
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                エントリーをキャンセルする
            </button>
        </form>

    @else
        {{-- 未エントリー → エントリーボタン --}}
        <form 
            action="{{ route('user.entries.entry', ['event' => $event->id]) }}" 
            method="POST"
            onsubmit="return confirmEntry();"
        >
            @csrf
            <button type="submit" class="bg-user text-white px-4 py-2 rounded hover:bg-user-dark transition">
                このイベントにエントリーする
            </button>
        </form>
    @endif

    <a href="{{ route('user.events.index') }}" class="inline-block bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">
        一覧に戻る
    </a>
</div>


<script>
function confirmCancel() {
    let message = 'このイベントのエントリーをキャンセルしますか？';
    @if($waitlistCount > 0)
        message += '\n現在キャンセル待ちの人がいます。再度エントリーするとキャンセル待ちの最後に登録されます。';
    @endif
    return confirm(message);
}

function confirmEntry() {
    let message = 'このイベントにエントリーしますか？';
    @if($isFull && $waitlistCount > 0)
        message += '\n満員のため、キャンセル待ちに登録されます。';
    @elseif($isFull)
        message += '\n満員のため、キャンセル待ちに登録されます。';
    @else
        message += '\n定員に余裕があるため、通常エントリーされます。';
    @endif
    return confirm(message);
}
</script>

</div>
@endsection
