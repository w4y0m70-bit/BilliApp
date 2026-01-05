@extends('admin.layouts.app')

@section('title', $isReplicate ? 'イベント作成（複製）' : 'イベント編集')

@section('content')
<h2 class="text-2xl font-bold mb-6">{{ $isReplicate ? 'イベント作成（複製）' : 'イベント編集' }}</h2>

<form action="{{ $formAction }}" method="POST"
      class="bg-white p-6 rounded-lg shadow w-full max-w-lg">
    @csrf
    @method($formMethod)

    {{-- イベント名（常に編集可） --}}
    <div class="mb-4">
        <div class="flex items-center mb-1">
            <label class="block font-medium mb-1">イベント名</label>
            <x-help help-key="admin.events.title" />
        </div>
        <input type="text" name="title" class="w-full border p-2 rounded"
               value="{{ old('title', $event->title) }}" required>
    </div>

    {{-- 開催日時 --}}
    <div class="mb-4">
        <div class="flex items-center mb-1">
            <label class="block font-medium mb-1">開催日時</label>
            <x-help help-key="admin.events.event_date" />
        </div>
        <input id="event_date" type="datetime-local" name="event_date"
            class="border w-full p-2 rounded
                @if($isLimited)
                    bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed
                @endif"
            value="{{ old('event_date', $event->event_date?->format('Y-m-d\TH:i')) }}"
            @if($isLimited) disabled @endif>
    </div>

    {{-- エントリー締め切り --}}
    <div class="mb-4">
        <div class="flex items-center mb-1">
            <label class="block font-medium mb-1">エントリー締め切り日時</label>
            <x-help help-key="admin.events.entry_deadline" />
        </div>
        <input id="entry_deadline" type="datetime-local" name="entry_deadline"
            class="w-full border p-2 rounded
                @if($isLimited)
                    bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed
                @endif"
            value="{{ old('entry_deadline', $event->entry_deadline?->format('Y-m-d\TH:i')) }}"
            @if($isLimited) disabled @endif>
    </div>

    {{-- 公開日時 --}}
    <div class="mb-4">
        <div class="flex items-center mb-1">
            <label class="block font-medium mb-1">公開日時</label>
            <x-help help-key="admin.events.published_at" />
        </div>
        <input id="published_at" type="datetime-local" name="published_at"
            class="border w-full p-2 rounded
                @if($isLimited)
                    bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed
                @endif"
            value="{{ old('published_at', $event->published_at?->format('Y-m-d\TH:i')) }}"
            @if($isLimited) disabled @endif>
        <small class="text-gray-500">設定した日時に公開されます</small>
    </div>

    {{-- 内容（常に編集可） --}}
    <div class="mb-4">
        <div class="flex items-center mb-1">
            <label class="block font-medium mb-1">イベント内容</label>
            <x-help help-key="admin.events.description" />
        </div>
        <textarea name="description" rows="4" class="w-full border p-2 rounded">{{ old('description', $event->description) }}</textarea>
        <small class="text-gray-500">イベントの詳細や、ルールなど参加者への説明</small>
    </div>

    {{-- 最大人数 --}}
    <div class="mb-4">
        <div class="flex items-center mb-1">
            <label class="block font-medium mb-1">最大人数</label>
            <x-help help-key="admin.events.max_participants" />
        </div>
        <input type="number" min="1" name="max_participants"
            class="w-full border p-2 rounded
                @if($isLimited)
                    bg-gray-100 text-gray-500 border-gray-300 cursor-not-allowed
                @endif"
            value="{{ old('max_participants', $event->max_participants) }}"
            @if($isLimited) disabled @endif>
    </div>

    {{-- キャンセル待ち --}}
    <div class="mb-4">
        <div class="flex items-center mb-1">
        <label class="block font-medium mb-1">
            キャンセル待ち
            @if($isLimited)
                <span class="text-sm text-gray-500">(公開済みのため編集不可)</span>
            @endif
        </label>
            <x-help help-key="admin.events.allow_waitlist" />
        </div>

        <div class="flex gap-6">

            <label class="flex items-center gap-2
                @if($isLimited) text-gray-400 cursor-not-allowed @endif">
                <input type="radio" name="allow_waitlist" value="1"
                    @if($event->allow_waitlist) checked @endif
                    @if($isLimited) disabled @endif>
                有
            </label>

            <label class="flex items-center gap-2
                @if($isLimited) text-gray-400 cursor-not-allowed @endif">
                <input type="radio" name="allow_waitlist" value="0"
                    @if(!$event->allow_waitlist) checked @endif
                    @if($isLimited) disabled @endif>
                無
            </label>

        </div>
    </div>

    {{-- ボタン --}}
    <button type="submit" class="bg-admin text-white px-4 py-2 rounded hover:bg-admin-dark">
        {{ $isReplicate ? '作成' : '更新' }}
    </button>
    <a href="{{ route('admin.events.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">
        キャンセル
    </a>
</form>

{{-- 削除ボタン（複製時は表示しない） --}}
@if(!$isReplicate)
<div class="mt-6 border-t pt-4">
    <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST"
          onsubmit="return confirm('本当に削除しますか？');">
        @csrf
        @method('DELETE')
        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            このイベントを削除する
        </button>
        <div help-key="admin.events.delete" class="inline-block ml-2">
            <x-help help-key="admin.events.delete" />
        </div>
    </form>
</div>
@endif


<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventInput = document.getElementById('event_date');
    const deadlineInput = document.getElementById('entry_deadline');
    const publishedInput = document.getElementById('published_at');

    if (!eventInput || !deadlineInput) return;

    const pad = num => num.toString().padStart(2, '0');

    const toDatetimeLocal = date => {
        return date.getFullYear() + '-' +
            pad(date.getMonth()+1) + '-' +
            pad(date.getDate()) + 'T' +
            pad(date.getHours()) + ':' +
            pad(date.getMinutes());
    };

    const now = new Date();

    eventInput.addEventListener('change', function() {
        const eventDate = new Date(this.value);
        if (!isNaN(eventDate)) {
            let deadline = new Date(eventDate.getTime() - 24*60*60*1000);
            if (deadline < now) deadline = now;
            deadlineInput.value = toDatetimeLocal(deadline);
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const eventDate = new Date(eventInput.value);
        const deadline = new Date(deadlineInput.value);

        if (deadline > eventDate) {
            alert('エントリー締め切りは開催日時より前にしてください');
            e.preventDefault();
        }
    });
});
</script>

@endsection
