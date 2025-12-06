@extends('user.layouts.app')

@section('title', $event->title)

@section('content')
@php
    // 初期値：waitlist_until がある場合はその値、なければ entry_deadline
    $waitlistDefault = $userEntry && $userEntry->waitlist_until
        ? $userEntry->waitlist_until->format('Y-m-d\TH:i')
        : $event->entry_deadline->format('Y-m-d\TH:i');
    $currentUser = Auth::user() ?? \App\Models\User::first();
    $userEntry = $event->userEntries()
        ->where('user_id', $currentUser->id)
        ->where('status', '!=', 'cancelled')
        ->latest('created_at')
        ->first();

    $isFull = $event->entry_count >= $event->max_participants;
    $canWaitlist = $event->allow_waitlist;
    $canEntry = !$isFull || ($isFull && $canWaitlist);
@endphp

<div class="bg-white shadow rounded-lg p-6">

    {{-- 主催者 --}}
    <p class="text-sm text-gray-600 mb-1">
        ［{{ $event->organizer->name ?? '主催者不明' }}］
    </p>

    {{-- タイトル --}}
    <h2 class="text-2xl font-bold mb-4">{{ $event->title }}</h2>


    {{-- ■ メインフォーム（エントリー or 変更） --}}
    <form action="{{ $userEntry
            ? route('user.entries.update', ['event' => $event->id, 'entry' => $userEntry->id])
            : route('user.entries.entry', ['event' => $event->id]) }}"
        method="POST"
        class="space-y-4">
        
        @csrf
        @if($userEntry)
            @method('PATCH')
        @endif

        {{-- イベント基本情報 --}}
        <div class="text-sm text-gray-700 space-y-1">
            <p><strong>開催日時：</strong><span class="text-lg font-bold">{{ format_event_date($event->event_date) }} {{ $event->event_date->format('H:i') }}</span></p>
            <p><strong>エントリー締切：</strong>{{ format_event_date($event->entry_deadline) }} {{ $event->entry_deadline->format('H:i') }}</p>
        </div>


        {{-- ■ キャンセル待ち期限（waitlist） --}}
        @if($userEntry && $userEntry->status === 'waitlist')

            <div 
                x-data="{
                    openModal:false,
                    waitlistUntil:`{{ $waitlistDefault }}`,
                }"
                class="mt-4 border p-4 rounded-lg bg-gray-50"
            >
                <p class="text-sm text-gray-700">
                    キャンセル待ち期限：
                    @if($userEntry->waitlist_until)
                        {{ format_event_date($userEntry->waitlist_until) }}
                        {{ $userEntry->waitlist_until->format('H:i') }}
                        <br><span class="text-red-600">（上記の日時に自動的にエントリーがキャンセルされます）</span>
                    @else
                        —
                    @endif
                </p>

                <button 
                    type="button"
                    @click="openModal = true"
                    class="mt-1 text-center w-full text-gray-600 underline"
                >
                    設定
                </button>


                {{-- ▼ モーダル --}}
                <template x-if="openModal">
                    <div class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-40 z-50">

                        <div class="bg-white p-6 rounded-lg shadow-lg w-80">
                            <h3 class="text-lg font-semibold mb-4">キャンセル待ち期限の設定</h3>

                            <form
                                method="POST"
                                action="{{ route('user.entries.update', ['event' => $event->id, 'entry' => $userEntry->id]) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <input 
                                    type="datetime-local" 
                                    name="waitlist_until"
                                    x-model="waitlistUntil"
                                    class="border rounded px-3 py-2 w-full mb-4"
                                >

                                <div class="flex justify-between items-center">

                                    <button
                                        type="submit"
                                        name="clear"
                                        value="1"
                                        class="bg-gray-300 text-gray-800 px-3 py-2 rounded hover:bg-gray-400"
                                    >
                                        期限をクリア
                                    </button>

                                    <button
                                        type="submit"
                                        class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700"
                                    >
                                        保存
                                    </button>
                                </div>
                            </form>

                            <button
                                @click="openModal = false"
                                class="mt-4 text-center w-full text-gray-600 underline"
                            >
                                閉じる
                            </button>
                        </div>

                    </div>
                </template>

            </div>
        @endif


        {{-- ■ イベント概要 --}}
        @if(!empty($event->description))
            <div class="mt-4">
                <p class="text-gray-700 break-words border-t pt-4">
                    <strong class="text-sm">【イベント内容】</strong><br>
                    {{ $event->description }}
                </p>
            </div>
        @endif

        {{-- ■ エントリーボタン --}}
        <div class="flex justify-end items-center gap-3 pt-4 border-t">
            @if(!$userEntry)
                <button 
                    type="submit"
                    class="px-4 py-2 rounded text-white transition
                        {{ $canEntry ? 'bg-user hover:bg-user-dark' : 'bg-gray-400 cursor-not-allowed' }}"
                    {{ $canEntry ? '' : 'disabled' }}
                >
                    @if(!$canEntry)
                        満員のためエントリー不可
                    @else
                        このイベントにエントリー
                    @endif
                </button>
            @endif


            <a href="{{ route('user.events.index') }}"
                class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">
                戻る
            </a>
        </div>

    </form>


    {{-- キャンセルボタン --}}
    @if($userEntry)
        <form 
            action="{{ route('user.entries.cancel', ['event' => $event->id, 'entryId' => $userEntry->id]) }}"
            method="POST"
            class="mt-3"
            onsubmit="return confirmCancel();"
        >
            @csrf
            @method('PATCH')

            <button
                type="submit"
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
            >
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
</script>
@endsection
