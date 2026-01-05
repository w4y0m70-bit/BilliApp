@extends('user.layouts.app')

@section('title', $event->title)

@section('content')
@php
    $currentUser = Auth::user() ?? \App\Models\User::first();
    $userEntry = $event->userEntries()
        ->where('user_id', $currentUser->id)
        ->where('status', '!=', 'cancelled')
        ->latest('created_at')
        ->first();

    $waitlistDefault = $userEntry && $userEntry->waitlist_until
        ? $userEntry->waitlist_until->format('Y-m-d\TH:i')
        : $event->entry_deadline->format('Y-m-d\TH:i');

    $isFull = $event->entry_count >= $event->max_participants;
    $canWaitlist = $event->allow_waitlist;
    $canEntry = !$isFull || ($isFull && $canWaitlist);
@endphp

{{-- セッションメッセージ用モーダル（自動オープン） --}}
<div x-data="{ open: false }" x-init="open = {{ session('message') || session('error') ? 'true' : 'false' }}">
    <template x-if="open">
        <div class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-40 z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-80">
                <h3 class="text-lg font-semibold mb-4 text-center">
                    @if(session('message')) 通知 @elseif(session('error')) エラー @endif
                </h3>
                <p class="text-gray-700 mb-4 text-center">
                    {{ session('message') ?? session('error') }}
                </p>
                <button @click="open = false" class="mt-2 text-center w-full bg-gray-300 text-gray-800 px-3 py-2 rounded hover:bg-gray-400 transition">
                    閉じる
                </button>
            </div>
        </div>
    </template>
</div>

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
            <div class="flex items-center">
            <strong>開催日時：</strong><span class="text-lg font-bold">{{ format_event_date($event->event_date) }} {{ $event->event_date->format('H:i') }}</span>
            </div>
            <div>
            <strong>エントリー締切：</strong>{{ format_event_date($event->entry_deadline) }} {{ $event->entry_deadline->format('H:i') }}
            </div>
            <div class="flex items-center">
                <strong>キャンセル待ち期限：</strong>
                <span class="ml-1">
                    @if ($status === 'waitlist' && $userEntry->waitlist_until)
                        {{ format_event_date($userEntry->waitlist_until) }}
                        {{ $userEntry->waitlist_until->format('H:i') }}
                    @else
                        —
                    @endif
                </span>
                <!-- <span help-key="user.events.waitlist_until" class="inline-block ml-2"> -->
                   <x-help help-key="user.events.waitlist_until" />
               <!-- </span> -->
               </div>
        </div>

        {{-- ■ キャンセル待ち期限（waitlist） --}}
        @if($userEntry && $userEntry->status === 'waitlist')
<x-modal
    title="キャンセル待ち期限の設定"
    confirm-text="保存"
    confirm-color="bg-user"
    :confirm-action="route('user.entries.update', ['event' => $event->id, 'entry' => $userEntry->id])"
>
    <x-slot name="form">
        <input
            type="datetime-local"
            name="waitlist_until"
            class="border rounded px-3 py-2 w-full mb-4"
            value="{{ $waitlistDefault }}"
        >

        <button
            type="submit"
            name="clear"
            value="1"
            class="bg-gray-300 text-gray-800 px-3 py-2 rounded hover:bg-gray-400 mb-2 w-full"
        >
            キャンセル待ち期限を設定しない
        </button>
    </x-slot>

    <x-slot name="trigger">
        <button
            type="button"
            @click="open = true"
            class="mt-1 text-center w-full text-gray-600 underline"
        >
            キャンセル待ち期限の設定・変更をする
        </button>
    </x-slot>
</x-modal>
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
            <span help-key="user.events.entry" class="inline-block">
                <x-help help-key="user.events.entry" />
            </span>
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

    {{-- ■ キャンセルボタン（モーダル化） --}}
    @if($userEntry)
        <x-modal
            title="確認"
            confirm-text="キャンセル"
            confirm-color="bg-red-600"
            :confirm-action="route('user.entries.cancel', ['event' => $event->id, 'entryId' => $userEntry->id])"
        >
            このイベントのエントリーをキャンセルしますか？
            @if($event->userEntries()->where('status','waitlist')->count() > 0)
                <br>現在キャンセル待ちの人がいます。再度エントリーするとキャンセル待ちの最後に登録されます。
            @endif
            <x-slot name="trigger">
                <button
                    type="button"
                    @click="open = true"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition mt-3"
                >
                    エントリーをキャンセル
                </button>
                <span help-key="user.events.cancel" class="inline-block ml-2">
                    <x-help help-key="user.events.cancel" />
                </span>
            </x-slot>
        </x-modal>
    @endif
</div>
@endsection
