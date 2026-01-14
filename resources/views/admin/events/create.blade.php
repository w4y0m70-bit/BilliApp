@extends('admin.layouts.app')

@section('title', '新規イベント作成')

@section('content')
<h2 class="text-2xl font-bold mb-6">新規イベント作成</h2>

<form action="{{ route('admin.events.confirm') }}" method="POST" class="bg-white p-6 rounded-lg shadow w-full max-w-lg">
    @csrf

    <div class="mb-4">
        <div class="flex items-center mb-1">
        <label class="font-medium">イベント名</label>
            <x-help help-key="admin.events.title" />
        </div>
        <input type="text" name="title" value="{{ old('title', $data['title'] ?? '') }}" class="w-full border p-2 rounded" required>
    </div>

    <div class="mb-4">
        <div class="flex items-center mb-1">
        <label class="font-medium">開催日時</label>
            <x-help help-key="admin.events.event_date" />
        </div>
        <input type="datetime-local" name="event_date" id="event_date" class="w-full border p-2 rounded" required>
        <small class="text-gray-500">公開後は変更できません</small>
    </div>

    <div class="mb-4">
        <div class="flex items-center mb-1">
        <label class="font-medium">エントリー締め切り日時</label>
            <x-help help-key="admin.events.entry_deadline" />
        </div>
        <input type="datetime-local" name="entry_deadline" id="entry_deadline" class="w-full border p-2 rounded" required>
        <small class="text-gray-500">公開後は変更できません</small>
    </div>

    <div class="mb-4">
        <div class="flex items-center mb-1">
        <label class="font-medium">公開日時</label>
            <x-help help-key="admin.events.published_at" />
        </div>
        <input type="datetime-local" name="published_at" id="published_at" class="border w-full p-2 rounded">
        <!-- <small class="text-gray-500">公開されるとイベントチケットが消費されます</small> -->
    </div>

    <div class="mb-4">
        <div class="flex items-center mb-1">
        <label class="block font-medium mb-1">イベント内容</label>
            <x-help help-key="admin.events.description" />
        </div>
        <textarea name="description" rows="4" class="w-full border p-2 rounded">{{ old('description', $data['description'] ?? '') }}</textarea>
    </div>

    <div class="mb-4">
        <div class="flex items-center mb-1">
        <label class="block font-medium mb-1">最大人数</label>
            <x-help help-key="admin.events.max_participants" />
        </div>
        <input type="number" name="max_participants" value="{{ old('max_participants', $data['max_participants'] ?? '') }}" class="w-full border p-2 rounded" min="1" required>
        <small class="text-gray-500">公開後は変更できません</small>
    </div>

    <div class="mb-4">
        <div class="flex items-center mb-1">
        <label class="block font-medium mb-1">キャンセル待ち</label>
            <x-help help-key="admin.events.allow_waitlist" />
        </div>
        <div class="flex gap-6">
            <label><input type="radio" name="allow_waitlist" value="1" checked> 有</label>
            <label><input type="radio" name="allow_waitlist" value="0"> 無</label>
        </div>
        <small class="text-gray-500">公開後は変更できません</small>
    </div>
    
    <div class="flex items-center mb-0">
        <label class="block font-medium mb-1">募集クラスの設定</label>
        <x-help help-key="admin.events.classes" />
    </div>
    <div class="mb-4 bg-gray-50 p-4 rounded-lg border">
        <div class="grid grid-cols-4 gap-2">
            @foreach(['P','SA', 'A', 'SB', 'B', 'C', 'Bg','L'] as $cls)
                <label class="flex items-center gap-2 cursor-pointer bg-white p-2 border rounded hover:bg-gray-100">
                    <input type="checkbox" name="classes[]" value="{{ $cls }}" 
                        {{ (is_array(old('classes')) && in_array($cls, old('classes'))) ? 'checked' : '' }}>
                    <span>{{ $cls }}</span>
                </label>
            @endforeach
        </div>
        <small class="text-gray-500 block mt-1">エントリー時にユーザーが選択できるクラスを指定します</small>
    </div>

    <div class="mb-6">
        <div class="flex items-center mb-1">
            <label class="font-medium">ユーザーへの追加質問・伝達事項</label>
            <x-help help-key="admin.events.instruction_label" />
        </div>
        <input type="text" name="instruction_label" 
            value="{{ old('instruction_label', $data['instruction_label'] ?? '') }}" 
            class="w-full border p-2 rounded" 
            placeholder="例：所属店舗を入力してください / Fargo Rateを入力してください">
        <small class="text-gray-500">空欄にすると、エントリーフォームに入力欄は表示されません</small>
    </div>
    <button type="submit" class="bg-admin text-white px-6 py-2 rounded hover:bg-admin-dark">
        確認画面へ
    </button>
    <a href="{{ route('admin.events.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500">
        キャンセル
    </a>
</form>

<!-- JavaScript -->
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

    // 公開日時に現在日時を初期値
    const tomorrowNoon = new Date();
    tomorrowNoon.setDate(tomorrowNoon.getDate() + 1);
    tomorrowNoon.setHours(12, 0, 0, 0);

    publishedInput.value = toDatetimeLocal(tomorrowNoon);

    // 開催日時入力時に締め切り日時を自動設定（1日前）
    eventInput.addEventListener('change', function() {
        const eventDate = new Date(this.value);
        if(!isNaN(eventDate)) {
            let deadline = new Date(eventDate.getTime() - 24*60*60*1000); // 1日前

            // 締め切りが過去なら現在日時に調整
            if(deadline < now) deadline = now;

            deadlineInput.value = toDatetimeLocal(deadline);
        }
    });

    // フォーム送信前にバリデーション
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
