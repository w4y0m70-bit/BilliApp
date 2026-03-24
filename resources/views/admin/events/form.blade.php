@extends('admin.layouts.app')

@section('title', $isReplicate ? 'イベント複製' : 'イベント編集')

@section('content')

    <x-form.section :title="$isReplicate ? 'イベント複製' : 'イベント編集'" type="admin" :errors="$errors" max-w-full>
        {{-- メインフォーム --}}
        <form id="event-form" action="{{ route('admin.events.confirm') }}" method="POST">
            @csrf

            {{-- 編集・複製時のID引き継ぎ（新規時は無し） --}}
            @if (isset($event->id) && !($isReplicate ?? false))
                <input type="hidden" name="id" value="{{ $event->id }}">
            @endif

            {{-- 共通フィールドの読み込み --}}
            @include('admin.events.partials.form-fields')

            {{-- 下部アクションボタン --}}
            <div class="mt-8 pt-6 border-t border-gray-100 flex gap-4">
                <button type="submit" class="bg-admin text-white px-8 py-2.5 rounded shadow hover:bg-admin-dark transition">
                    確認画面へ
                </button>
                <a href="{{ route('admin.events.index') }}"
                    class="bg-gray-400 text-white px-8 py-2.5 rounded shadow hover:bg-gray-500 transition text-center">
                    キャンセル
                </a>
            </div>
        </form>

        {{-- 削除エリア（未公開の場合のみ表示） --}}
        @php
            $now = now();
            $alreadyPublished = $event->published_at && $event->published_at <= $now;
        @endphp

        @if (isset($event->id) && !$isReplicate && !$alreadyPublished)
            <div class="mt-12 pt-8 border-t-2 border-red-50 relative">
                <span class="absolute -top-3 left-4 bg-white px-2 text-xs font-bold text-red-500">DANGER ZONE</span>

                <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST"
                    onsubmit="return confirm('本当に削除してもよろしいですか？\nこの操作は取り消せません。');">
                    @csrf
                    @method('DELETE')

                    <div
                        class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-red-50 p-4 rounded-lg border border-red-100">
                        <div>
                            <p class="text-sm font-bold text-red-700">イベントを完全に削除する</p>
                            <p class="text-xs text-red-600 mt-1">一度削除したイベントは元に戻せません（未公開チケットは返却されます）</p>
                        </div>
                        <button type="submit"
                            class="bg-red-500 text-white px-4 py-2 text-sm rounded hover:bg-red-700 transition shadow-sm whitespace-nowrap">
                            完全に削除する
                        </button>
                    </div>
                </form>
            </div>
        @endif

    </x-form.section>

    @include('admin.events.partials.form-scripts')
@endsection
