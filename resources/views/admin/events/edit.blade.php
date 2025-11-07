@extends('admin.layouts.app')

@section('title', 'イベント編集')

@section('content')
<h2 class="text-2xl font-bold mb-6">イベント編集</h2>

<form action="{{ route('admin.events.update', $event->id) }}" method="POST" class="bg-white p-6 rounded-lg shadow w-full max-w-lg">
    @csrf
    @method('PUT')

    <div class="mb-4">
        <label class="block font-medium mb-1">イベント名</label>
        <input type="text" name="title" class="w-full border p-2 rounded" value="{{ old('title', $event->title) }}" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium mb-1">開催日時</label>
        <input type="datetime-local" name="event_date" id="event_date" class="w-full border p-2 rounded" 
               value="{{ old('event_date', $event->event_date->format('Y-m-d\TH:i')) }}" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium mb-1">エントリー締め切り日時</label>
        <input type="datetime-local" name="entry_deadline" id="entry_deadline" class="w-full border p-2 rounded"
               value="{{ old('entry_deadline', $event->entry_deadline->format('Y-m-d\TH:i')) }}" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium mb-1">公開日時</label>
        <input type="datetime-local" name="published_at" id="published_at" class="border w-full p-2 rounded"
               value="{{ old('published_at', $event->published_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}">
        <small class="text-gray-500">設定した日時に公開されます</small>
    </div>

    <div class="mb-4">
        <label class="block font-medium mb-1">イベント内容</label>
        <textarea name="description" rows="4" class="w-full border p-2 rounded">{{ old('description', $event->description) }}</textarea>
    </div>

    <div class="mb-4">
        <label class="block font-medium mb-1">最大人数</label>
        <input type="number" name="max_participants" class="w-full border p-2 rounded" min="1" 
               value="{{ old('max_participants', $event->max_participants) }}" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium mb-1">キャンセル待ち</label>
        <div class="flex gap-6">
            <label><input type="radio" name="allow_waitlist" value="1" {{ old('allow_waitlist', $event->allow_waitlist) ? 'checked' : '' }}> 有</label>
            <label><input type="radio" name="allow_waitlist" value="0" {{ !old('allow_waitlist', $event->allow_waitlist) ? 'checked' : '' }}> 無</label>
        </div>
    </div>

    <button type="submit" class="bg-admin text-white px-6 py-2 rounded hover:bg-admin-dark">
        更新する
    </button>
    <a href="{{ route('admin.events.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">
        キャンセル
    </a>
</form>

<div class="mt-6 border-t pt-4">
    <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
        @csrf
        @method('DELETE')
        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
            このイベントを削除する
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventInput = document.getElementById('event_date');
    const deadlineInput = document.getElementById('entry_deadline');
    const publishedInput = document.getElementById('published_at');

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
        if(!isNaN(eventDate)) {
            let deadline = new Date(eventDate.getTime() - 24*60*60*1000);
            if(deadline < now) deadline = now;
            deadlineInput.value = toDatetimeLocal(deadline);
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const eventDate = new Date(eventInput.value);
        const deadline = new Date(deadlineInput.value);

        if(deadline > eventDate) {
            alert('エントリー締め切りは開催日時より前にしてください');
            e.preventDefault();
        }
        if(deadline < now) {
            alert('エントリー締め切りは過去に設定できません');
            e.preventDefault();
        }
    });
});
</script>
@endsection
