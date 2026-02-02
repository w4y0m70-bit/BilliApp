@extends('admin.layouts.app')

@section('title', 'イベント一覧')

@section('content')
<div class="px-4">
    <h2 class="text-2xl font-bold mb-2">イベント一覧<x-help help-key="admin.events.index" /></h2>

    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center space-x-2">
            <h3 class="text-xl font-semibold">公開中のイベント</h3>
            <x-help help-key="admin.events.index.published_events" />
        </div>
        <div>
            <x-help help-key="admin.events.index.create_event" />
            <a href="{{ route('admin.events.create') }}"
            class="bg-admin text-white px-4 py-2 rounded hover:bg-admin-dark">
                イベント作成
            </a>
        </div>
    </div>
    
    @if($publishedEvents->count() > 0)
        @include('admin.events.partials.event-table', ['events' => $publishedEvents])
    @else
        <p class="bg-gray-50 border border-dashed border-gray-300 text-gray-500 p-8 text-center rounded-lg">
            公開中のイベントはありません
        </p>
    @endif

    <div>
        <h3 class="text-xl font-semibold mt-6 mb-2">未公開のイベント
            <x-help help-key="admin.events.index.unpublished_events" />
        </h3>
    </div>
    
    @if($unpublishedEvents->count() > 0)
        @include('admin.events.partials.event-table', ['events' => $unpublishedEvents])
    @else
        <p class="bg-gray-50 border border-dashed border-gray-300 text-gray-500 p-8 text-center rounded-lg">
            未公開のイベントはありません
        </p>
    @endif

    <div x-data="{ open: false }" class="mt-6">
        <div class="flex items-center mb-2">
            <button @click="open = !open" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">
                過去のイベントを表示
                <span x-text="open ? '▲' : '▼'"></span>
            </button>
            <x-help help-key="admin.events.index.past_events" class="ml-2" />
        </div>
        <div x-show="open" class="mt-4" x-transition>
            @if($pastEvents->count() > 0)
                @include('admin.events.partials.event-table', [
                    'events' => $pastEvents,
                    'hideActions' => true
                ])
            @else
                <p class="bg-gray-50 border border-dashed border-gray-300 text-gray-500 p-8 text-center rounded-lg">
                    過去のイベントはありません
                </p>
            @endif
        </div>
    </div>
</div>
@endsection