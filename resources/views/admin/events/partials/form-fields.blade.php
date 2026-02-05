@csrf

{{-- イベント名 --}}
<div class="mb-4">
    <div class="flex items-center mb-1">
        <label class="font-medium">イベント名</label>
        <x-help help-key="admin.events.title" />
    </div>
    <input type="text" name="title" value="{{ old('title', $event->title ?? '') }}" class="w-full border p-2 rounded" required>
</div>

{{-- イベント内容 --}}
<div class="mb-4">
    <div class="flex items-center mb-1">
        <label class="block font-medium mb-1">イベント内容・詳細</label>
        <x-help help-key="admin.events.description" />
    </div>
    <textarea name="description" rows="4" class="w-full border p-2 rounded">{{ old('description', $event->description ?? '') }}</textarea>
</div>

{{-- 開催日時 --}}
<div class="mb-4">
    <div class="flex items-center mb-1">
        <label class="font-medium">開催日時</label>
        <x-help help-key="admin.events.event_date" />
    </div>
    <input type="datetime-local" name="event_date" id="event_date"
        value="{{ old('event_date', isset($event->event_date) ? $event->event_date->format('Y-m-d\TH:i') : '') }}"
        class="w-full border p-2 rounded @if($isLimited && !$isReplicate) bg-gray-100 text-gray-500 @endif"
        @if($isLimited && !$isReplicate) readonly @else required @endif>
    @if($isLimited && !$isReplicate)<small class="text-red-500">公開後は変更できません</small>@endif
</div>

{{-- エントリー締め切り --}}
<div class="mb-4">
    <div class="flex items-center mb-1">
        <label class="font-medium">エントリー締切日時</label>
        <x-help help-key="admin.events.entry_deadline" />
    </div>
    <input type="datetime-local" name="entry_deadline" id="entry_deadline"
        value="{{ old('entry_deadline', isset($event->entry_deadline) ? $event->entry_deadline->format('Y-m-d\TH:i') : '') }}"
        class="w-full border p-2 rounded @if($isLimited && !$isReplicate) bg-gray-100 text-gray-500 @endif"
        @if($isLimited && !$isReplicate) readonly @else required @endif>
</div>

{{-- 公開日時 --}}
<div class="mb-4">
    <div class="flex items-center mb-1">
        <label class="font-medium">イベントを公開する日時</label>
        <x-help help-key="admin.events.published_at" />
    </div>
    {{-- 複製時は $event->published_at を空にするため、isset判定を調整 --}}
    <input type="datetime-local" name="published_at" 
        value="{{ old('published_at', (isset($event->published_at) && !$isReplicate) ? $event->published_at->format('Y-m-d\TH:i') : '') }}" 
        id="published_at" class="border w-full p-2 rounded">
</div>

{{-- 最大人数 --}}
<div class="mb-4">
    <div class="flex items-center mb-1">
        <label class="font-medium">最大人数</label>
        <x-help help-key="admin.events.max_participants" />
    </div>
    <input type="number" name="max_participants" id="max_participants" 
        value="{{ old('max_participants', $event->max_participants ?? '') }}" 
        class="w-full border p-2 rounded @if($isLimited && !$isReplicate) bg-gray-100 @endif"
        min="1" @if($isLimited && !$isReplicate) readonly @else required @endif>
</div>

{{-- キャンセル待ち --}}
<div class="mb-4">
    <div class="flex items-center mb-1">
        <label class="block font-medium mb-1">キャンセル待ち</label>
        <x-help help-key="admin.events.allow_waitlist" />
    </div>
    <div class="flex gap-6">
        <label><input type="radio" name="allow_waitlist" value="1" {{ old('allow_waitlist', $event->allow_waitlist ?? 1) == 1 ? 'checked' : '' }}> 有</label>
        <label><input type="radio" name="allow_waitlist" value="0" {{ old('allow_waitlist', $event->allow_waitlist ?? 1) == 0 ? 'checked' : '' }}> 無</label>
    </div>
    <small class="text-gray-500">公開後は変更できません</small>
</div>

{{-- 募集クラス --}}
<div class="mb-4">
    <div class="flex items-center mb-0">
        <label class="block font-medium mb-1">募集クラスの設定</label>
        <x-help help-key="admin.events.classes" />
    </div>
    <div class="bg-gray-50 p-4 rounded-lg border grid grid-cols-4 gap-2">
        @php
            if (is_array(old('classes'))) {
                $selectedClasses = old('classes');
            } elseif (!empty($existingClasses)) {
                $selectedClasses = $existingClasses;
            } else {
                // 通常編集時にリレーションから直接取得
                $selectedClasses = isset($event) ? $event->eventClasses->pluck('class_name')->toArray() : [];
            }
        @endphp
        @foreach(['P','SA', 'A', 'SB', 'B', 'C', 'Bg','L'] as $cls)
        <label class="flex items-center gap-2 cursor-pointer bg-white p-2 border rounded">
            <input type="checkbox" name="classes[]" value="{{ $cls }}" 
                {{ in_array($cls, $selectedClasses) ? 'checked' : '' }}>
            <span>{{ $cls }}</span>
        </label>
        @endforeach
    </div>
    <small class="text-gray-500">ハンディキャップを設定したクラスを選択してください</small>
</div>

{{-- 追加質問 --}}
<div class="mb-6">
    <div class="flex items-center mb-1">
        <label class="font-medium">ユーザーへの追加質問・伝達事項</label>
        <x-help help-key="admin.events.instruction_label" />
    </div>
    <input type="text" name="instruction_label" 
        value="{{ old('instruction_label', $event->instruction_label ?? '') }}" 
        class="w-full border p-2 rounded" 
        placeholder="例：所属店舗を入力してください / ご質問・ご要望があればご記入ください">
    <small class="text-gray-500">空欄にすると、エントリーフォームに入力欄は表示されません</small>
</div>

{{-- チケット選択 --}}
<div class="mb-4 bg-blue-50 p-4 rounded-lg border border-blue-200">
    <div class="flex items-center mb-1">
        <label class="font-medium text-blue-800">使用するチケットの選択</label>
        <x-help help-key="admin.events.ticket_id" />
    </div>
    @if($isLimited && !$isReplicate)
        <div class="p-2 bg-white border rounded text-gray-600">{{ $event->ticket->plan->display_name ?? '選択済みチケット' }}</div>
        <input type="hidden" name="ticket_id" value="{{ $event->ticket_id }}">
    @else
        <select name="ticket_id" id="ticket_id" class="w-full border p-2 rounded bg-white" required>
            <option value="">-- 使用するチケットを選択 --</option>
            @foreach($availableTickets as $ticket)
                <option value="{{ $ticket->id }}" 
                    data-capacity="{{ $ticket->plan->max_capacity }}"
                    {{-- 
                        優先順位：
                        1. old('ticket_id') ... バリデーションエラーで戻った時
                        2. $event->ticket_id ... DBに保存されている値
                    --}}
                    @if(old('ticket_id', $event->ticket_id ?? '') == $ticket->id) selected @endif
                >
                    {{ $ticket->plan->display_name }} (上限{{ $ticket->plan->max_capacity }}名)
                </option>
            @endforeach
        </select>
    @endif
</div>