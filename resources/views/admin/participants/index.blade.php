@extends('admin.layouts.app')

@section('title', $event->title . ' 参加者一覧')

@section('content')
    <h2 class="text-2xl font-bold mb-6">{{ $event->title }} の参加者一覧</h2>

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('admin.events.index') }}" class="text-gray-500 hover:underline">
            ← イベント一覧へ戻る
        </a>

        <!-- ゲスト登録ボタン -->
        <a href="{{ route('admin.participants.create', $event->id) }}"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            ゲスト登録
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @forelse($participants as $entry)
            <div
                class="shadow rounded-lg p-4 flex flex-col items-center
                    {{ $entry->status == 'waitlist' ? 'bg-yellow-100' : 'bg-white' }}">
                <div class="text-lg font-semibold">{{ $entry->user->name }}</div>
                <div class="{{ $entry->status == 'waitlist' ? 'text-yellow-800' : 'text-gray-500' }}">
                    {{ $entry->status == 'waitlist' ? 'キャンセル待ち' : '参加' }}
                </div>
                <div class="text-sm text-gray-400 mt-2">登録日: {{ $entry->created_at->format('Y-m-d H:i') }}</div>
            </div>
        @empty
            <p class="col-span-3 text-center text-gray-500">参加者はいません</p>
        @endforelse
    </div>
@endsection
