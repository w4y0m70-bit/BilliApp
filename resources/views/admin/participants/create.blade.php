@extends('admin.layouts.app')

@section('title', 'ゲスト登録')

@section('content')
<h2 class="text-2xl font-bold mb-6">{{ $event->title }} - ゲスト登録</h2>

<a href="{{ route('admin.events.participants.index', $event->id) }}" class="text-gray-500 hover:underline mb-4 inline-block">
    ← 参加者一覧へ戻る
</a>

<form action="{{ route('admin.participants.store', $event->id) }}" method="POST" class="bg-white p-6 rounded-lg shadow w-full max-w-md">
    @csrf
    <div class="mb-4">
        <label class="block font-medium mb-1">名前</label>
        <input type="text" name="name" class="border w-full p-2 rounded" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium mb-1">ステータス</label>
        <select name="status" class="border w-full p-2 rounded">
            <option value="participant" selected>参加</option>
            <option value="waitlist">キャンセル待ち</option>
        </select>
    </div>

    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
        登録
    </button>
</form>
@endsection
