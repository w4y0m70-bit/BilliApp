@extends('user.layouts.app')

@section('title', '参加者一覧')

@section('content')
<div class="px-4">
    <div class="mb-4">
        <a href="{{ route('user.events.index') }}" class="text-blue-600 hover:underline">← イベント一覧に戻る</a>
    </div>

    <h2 class="text-2xl font-bold mb-2">参加者一覧
        <x-help help-key="user.participants.index" />
    </h2>
    <p class="text-gray-600 mb-6">イベント名：{{ $event->title }}</p>

    <x-event.participant-list 
        :event="$event" 
        :participants="$event->userEntries()->where('status', '!=', 'cancelled')->orderBy('order', 'asc')->get()"
        :max-entries="$event->max_entries"
        mode="user" 
    />
</div>
@endsection