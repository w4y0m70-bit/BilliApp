@extends('user.layouts.app')

@section('title', $event->title)

@section('content')
@php
    $currentUser = Auth::user() ?? \App\Models\User::first();
    $userEntry = $event->userEntries()
        ->where(function($query) use ($currentUser) {
            $query->where('representative_user_id', $currentUser->id)
                  ->orWhereHas('members', function($q) use ($currentUser) {
                      $q->where('user_id', $currentUser->id);
                  });
        })
        ->where('status', '!=', 'cancelled')
        ->latest('created_at')
        ->first();

    $waitlistDefault = $userEntry && $userEntry->waitlist_until
        ? $userEntry->waitlist_until->format('Y-m-d\TH:i')
        : $event->entry_deadline->format('Y-m-d\TH:i');

    $isFull = $event->entry_count >= $event->max_participants;
    $canWaitlist = $event->allow_waitlist;
    $isDeadlinePast = $event->entry_deadline->isPast();
    $canEntry = !$isDeadlinePast && (!$isFull || ($isFull && $canWaitlist));
    
    $status = $userEntry->status ?? null;
    // 自分が「招待されている側（未回答）」のレコードを取得
    $invitationEntry = $event->userEntries()
        ->whereHas('members', function($q) use ($currentUser) {
            $q->where('user_id', $currentUser->id)->where('invite_status', 'pending');
        })
        ->where('status', 'pending')
        ->first();

    $isInvited = (bool)$invitationEntry;
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
    {{-- ★ 招待されている場合のみ表示 --}}
    @if($isInvited)
        <div class="mb-6 bg-blue-50 border-2 border-blue-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-3 text-blue-800">
                <span class="material-symbols-outlined font-bold">group_add</span>
                <h3 class="text-lg font-bold">チーム出場の招待が届いています</h3>
            </div>
            
            <p class="text-sm text-gray-700 mb-4">
                <strong>{{ $invitationEntry->representative->full_name }}</strong> さんからチームの招待を受けています。<br>
                回答期限：<span class="text-red-600 font-bold">{{ $invitationEntry->pending_until->format('m/d H:i') }}</span>
            </p>

            <form action="{{ route('user.entries.respond', ['event' => $event->id, 'entry' => $invitationEntry->id]) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    {{-- クラス選択など、パートナーも入力が必要な項目 --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">あなたの出場クラス <span class="text-red-500">*</span></label>
                        <select name="class" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-user focus:ring-user">
                            <option value="">選択してください</option>
                            @foreach($event->eventClasses as $class)
                                <option value="{{ $class->class_name }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 mt-4">
                        <button type="submit" name="answer" value="approve" class="flex-1 bg-user text-white font-bold py-3 rounded-lg hover:bg-user-dark transition shadow-md">
                            招待を承諾してエントリー
                        </button>
                        
                        <button type="submit" name="answer" value="reject" onclick="return confirm('招待を辞退しますか？')" class="sm:w-1/3 bg-white text-red-600 border border-red-200 font-bold py-3 rounded-lg hover:bg-red-50 transition">
                            辞退する
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
    {{-- 主催者とグループ --}}
    <div x-data="{ showOrganizer: false }">
        <div class="flex justify-between items-start mb-1">
            <div class="flex flex-col">
                    【{{ $event->organizer->name ?? '主催者不明' }}】
            </div>

            {{-- グループ表示 --}}
            @if($event->requiredGroups->isNotEmpty())
                <div class="flex flex-wrap gap-1">
                    @foreach($event->requiredGroups as $group)
                        <span class="inline-flex items-center text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold border border-blue-200 shadow-sm">
                            {{ $group->name }}限定
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

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
                    <strong class="text-sm">《イベント詳細》</strong><br>
                    {!! nl2br(e($event->description)) !!}
                </p>
            </div>
        @endif

        {{-- ■ エントリーボタン --}}
        <div class="flex justify-end items-center gap-3 pt-4 border-t">
            @if(!$userEntry)
                <span help-key="user.events.entry" class="inline-block">
                    <x-help help-key="user.events.entry" />
                </span>
                
                @if($isDeadlinePast)
                    {{-- 期限切れの場合 --}}
                    <button type="button" disabled class="px-4 py-2 rounded text-white bg-gray-500 cursor-not-allowed">
                        エントリー締切
                    </button>
                @else
                    {{-- 期限内の場合 --}}
                    <a href="{{ $canEntry ? route('user.entries.create', $event->id) : '#' }}"
                       class="px-4 py-2 rounded text-white transition text-center
                              {{ $canEntry ? 'bg-user hover:bg-user-dark' : 'bg-gray-400 pointer-events-none' }}">
                        @if(!$canEntry && $isFull)
                            満員のためエントリー不可
                        @else
                            エントリー入力画面へ
                        @endif
                    </a>
                @endif
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
            confirm-text="エントリーをキャンセルする"
            confirm-color="bg-red-600"
            :confirm-action="route('user.entries.cancel', ['event' => $event->id, 'entryId' => $userEntry->id])"
        >
            このイベントのエントリーをキャンセルしますか？
            @if($event->waitlist_count > 0)
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
