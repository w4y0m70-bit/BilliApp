@extends('admin.layouts.app')

@section('title', 'イベント一覧')

@section('content')
    <div class="px-4 py-6 w-full" x-data="{ tab: 'published' }">
        @php $maxWidth = 'max-w-6xl'; @endphp

        {{-- ヘッダー --}}
        <div class="{{ $maxWidth }} mx-auto mb-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    イベント管理
                    <x-help help-key="admin.events.index" class="ml-2" />
                </h2>
                <a href="{{ route('admin.events.create') }}"
                    class="bg-admin text-white px-6 py-2 rounded-lg font-bold hover:bg-admin-dark shadow-md transition">
                    ＋ 新規イベント作成
                </a>
            </div>
        </div>

        {{-- タブメニュー --}}
        <div class="{{ $maxWidth }} mx-auto mb-6">
            <div class="flex border-b border-gray-200 space-x-8 overflow-x-auto">
                {{-- 公開中 --}}
                <button @click="tab = 'published'"
                    :class="tab === 'published' ? 'border-admin text-admin' :
                        'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-1 border-b-2 font-medium text-sm flex items-center whitespace-nowrap">
                    公開中
                    <span class="ml-2 py-0.5 px-2 rounded-full text-xs font-bold transition-colors"
                        :class="tab === 'published' ? 'bg-admin text-white' : 'bg-gray-200 text-gray-600'">
                        {{ $publishedEvents->count() }}
                    </span>
                </button>

                {{-- 未公開 --}}
                <button @click="tab = 'unpublished'"
                    :class="tab === 'unpublished' ? 'border-admin text-admin' :
                        'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-1 border-b-2 font-medium text-sm flex items-center whitespace-nowrap">
                    未公開
                    <span class="ml-2 py-0.5 px-2 rounded-full text-xs font-bold transition-colors"
                        :class="tab === 'unpublished' ? 'bg-admin text-white' : 'bg-gray-200 text-gray-600'">
                        {{ $unpublishedEvents->count() }}
                    </span>
                </button>

                {{-- 過去分 --}}
                <button @click="tab = 'past'"
                    :class="tab === 'past' ? 'border-admin text-admin' :
                        'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-1 border-b-2 font-medium text-sm flex items-center whitespace-nowrap">
                    過去
                    <span class="ml-2 py-0.5 px-2 rounded-full text-xs font-bold transition-colors"
                        :class="tab === 'past' ? 'bg-admin text-white' : 'bg-gray-200 text-gray-600'">
                        {{ $pastEvents->count() }}
                    </span>
                </button>
            </div>
        </div>

        {{-- コンテンツ --}}
        <div class="{{ $maxWidth }} mx-auto">
            {{-- 公開中 --}}
            <div x-show="tab === 'published'">
                @if ($publishedEvents->isNotEmpty())
                    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100">
                        @include('admin.events.partials.event-table', ['events' => $publishedEvents])
                    </div>
                @else
                    <div class="bg-gray-50 border border-dashed border-gray-300 text-gray-500 p-12 text-center rounded-lg">
                        公開中のイベントはありません
                    </div>
                @endif
            </div>

            {{-- 未公開 --}}
            <div x-show="tab === 'unpublished'" style="display: none;">
                @if ($unpublishedEvents->isNotEmpty())
                    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100">
                        @include('admin.events.partials.event-table', ['events' => $unpublishedEvents])
                    </div>
                @else
                    <div class="bg-gray-50 border border-dashed border-gray-300 text-gray-500 p-12 text-center rounded-lg">
                        未公開のイベントはありません
                    </div>
                @endif
            </div>

            {{-- 過去 --}}
            <div x-show="tab === 'past'" style="display: none;">
                @if ($pastEvents->isNotEmpty())
                    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100">
                        @include('admin.events.partials.event-table', [
                            'events' => $pastEvents,
                            'hideActions' => true,
                        ])
                    </div>
                @else
                    <div class="bg-gray-50 border border-dashed border-gray-300 text-gray-500 p-12 text-center rounded-lg">
                        過去のイベントはありません
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
