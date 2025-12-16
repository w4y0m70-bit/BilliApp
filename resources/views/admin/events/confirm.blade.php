@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">イベント確認</h1>

    <div class="bg-white shadow rounded p-6 mb-6">
        <!-- <h2 class="text-xl font-semibold mb-4">イベント情報</h2> -->

        <div class="mb-2">
            <label class="font-semibold">イベント名:</label>
            <div>{{ $data['title'] ?? '' }}</div>
        </div>

        <div class="mb-2">
            <label class="font-semibold">説明:</label>
            <div>{{ $data['description'] ?? '' }}</div>
        </div>

        <div class="mb-2">
            <label class="font-semibold">開催日時:</label>
            <div>
                @if(!empty($data['event_date']))
                    {{ \Carbon\Carbon::parse($data['event_date'])
                        ->locale('ja')
                        ->translatedFormat('Y-m-d（D） H:i') }}
                @else
                    —
                @endif
            </div>
        </div>

        <div class="mb-2">
            <label class="font-semibold">エントリー締切:</label>
            <div>
                @if(!empty($data['entry_deadline']))
                    {{ \Carbon\Carbon::parse($data['entry_deadline'])
                        ->locale('ja')
                        ->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}
                @else
                    —
                @endif
            </div>
        </div>

        <div class="mb-2">
            <label class="font-semibold">公開日時:</label>
            <div>
                @if(!empty($data['published_at']))
                    @php
                        $publishedAt = \Carbon\Carbon::parse($data['published_at'])->locale('ja');
                    @endphp

                    {{ $publishedAt->translatedFormat('Y-m-d（D） H:i') }}

                    @if($publishedAt->isPast())
                        <span class="ml-2 text-sm text-red-600">
                            （即時公開されます）
                        </span>
                    @endif
                @else
                    —
                @endif
            </div>
        </div>


        <div class="mb-2">
            <label class="font-semibold">最大参加者数:</label>
            <div>{{ $data['max_participants'] ?? '' }}</div>
        </div>

        <div class="mb-2">
            <label class="font-semibold">キャンセル待ち:</label>
            <div>{{ ($data['allow_waitlist'] ?? false) ? '有効' : '無効' }}</div>
        </div>
    </div>

    <div class="flex space-x-4">
        <form action="{{ route('admin.events.store') }}" method="POST">
            @csrf
            @foreach($data as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="bg-admin text-white px-4 py-2 rounded hover:bg-admin-dark">
                登録する
            </button>
        </form>

        <button
            type="button"
            onclick="history.back()"
            class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400"
        >
            戻る
        </button>
    </div>
</div>
@endsection
