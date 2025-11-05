@extends('admin.layouts.app')

@section('title', 'イベント一覧')

@section('content')
<h2 class="text-2xl font-bold mb-6">イベント一覧</h2>

<!-- 公開中のイベント -->
<h3 class="text-xl font-semibold mb-2">公開中のイベント</h3>
@include('admin.events.partials.event-table', ['events' => $publishedEvents])

<!-- 未公開のイベント -->
<h3 class="text-xl font-semibold mt-6 mb-2">未公開のイベント</h3>
@include('admin.events.partials.event-table', ['events' => $unpublishedEvents])

<!-- 過去のイベント（折りたたみ） -->
<div x-data="{ open: false }" class="mt-6">
    <button @click="open = !open" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
        過去のイベントを表示
        <span x-text="open ? '▲' : '▼'"></span>
    </button>
    <div x-show="open" class="mt-4" x-transition>
        @include('admin.events.partials.event-table', [
            'events' => $pastEvents,
            'hideActions' => true   {{-- 過去イベントなので編集ボタン非表示 --}}
        ])
    </div>
</div>
@endsection
