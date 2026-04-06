@csrf

<p class="text-xs text-gray-600 mb-4">
    <span class="text-red-500">＊</span>印のついた項目は、イベント公開後は変更できなくなります。
</p>

{{-- イベント名 --}}
<x-form.group label="イベント名" :is-limited-field="false" type="admin" help-key="admin.events.title">
    <x-text-input color-type="admin" name="title" type="text" :value="old('title', $event->title ?? '')" required />
</x-form.group>

{{-- イベント内容 --}}
<x-form.group label="イベント内容・詳細" type="admin" help-key="admin.events.description">
    <x-slot:labelAction>
        <button type="button" onclick="fillDefaultDescription()"
            class="text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded">入力例</button>
    </x-slot:labelAction>
    <x-textarea-input id="event-description" name="description" rows="4" color-type="admin" class="min-h-[200px]"
        minHeight="min-h-[200px]">
        {{ old('description', $event->description ?? '') }}
    </x-textarea-input>
</x-form.group>

{{-- 開催日時 現在より180日以内 --}}
<x-form.group label="開催日時" help-key="admin.events.event_date" :is-limited-field="true" type="admin">
    @php
        // 現在から180日後の日付を取得
        $maxDate = now()->addDays(180)->format('Y-m-d\TH:i');
        // 最小値（今日）も設定しておくとより親切です
        $minDate = now()->format('Y-m-d\TH:i');
    @endphp

    <x-text-input id="event_date" type="datetime-local" name="event_date" color-type="admin" :value="old('event_date', $event->event_date?->format('Y-m-d\TH:i') ?? '')"
        {{-- max と min を追加 --}} max="{{ $maxDate }}" min="{{ $minDate }}" :disabled="$isLimited && !$isReplicate"
        class="{{ $isLimited && !$isReplicate ? 'bg-gray-100 text-gray-500' : '' }}" />

    <x-slot:hint>
        @if ($isLimited && !$isReplicate)
            <span class="text-red-500 font-bold">※公開中のため変更できません</span>
        @endif
    </x-slot:hint>
</x-form.group>

{{-- エントリー締め切り --}}
<x-form.group label="エントリー締切日時" help-key="admin.events.entry_deadline" :is-limited-field="true" type="admin">
    <x-text-input id="entry_deadline" type="datetime-local" name="entry_deadline" color-type="admin" :value="old('entry_deadline', $event->entry_deadline?->format('Y-m-d\TH:i') ?? '')"
        :disabled="$isLimited && !$isReplicate" class="{{ $isLimited && !$isReplicate ? 'bg-gray-100 text-gray-500' : '' }}" />
    @if ($isLimited && !$isReplicate)
        <x-slot:hint><span class="text-red-500 font-bold">※公開中のため変更できません</span></x-slot:hint>
    @endif
</x-form.group>

{{-- 公開日時 --}}
<x-form.group label="イベント公開日時" help-key="admin.events.published_at" type="admin">
    <x-text-input id="published_at" type="datetime-local" name="published_at" color-type="admin" :value="old(
        'published_at',
        isset($event->published_at) && !$isReplicate ? $event->published_at->format('Y-m-d\TH:i') : '',
    )" />
</x-form.group>

{{-- 募集人数 --}}
<x-form.group label="募集人数（またはチーム数）" help-key="admin.events.max_entries" :is-limited-field="true" type="admin">
    <x-text-input type="number" name="max_entries" color-type="admin" min="1" :value="old('max_entries', $event->max_entries ?? '')"
        :disabled="$isLimited && !$isReplicate" class="{{ $isLimited && !$isReplicate ? 'bg-gray-100' : '' }}" />
    @if ($isLimited && !$isReplicate)
        <x-slot:hint><span class="text-red-500 font-bold">※公開中のため変更できません</span></x-slot:hint>
    @endif
</x-form.group>

{{-- エントリー形式（シングルス, ダブルス, トリオス［カルテット,クインテット］） --}}
<x-form.group label="エントリー形式" help-key="admin.events.entry_type" :is-limited-field="true" type="admin">

    <select name="max_team_size" id="max_team_size"
        class="w-full border border-gray-300 rounded-md p-2 transition duration-150 focus:outline-none focus:ring-2 focus:border-admin focus:ring-admin 
               {{ $isLimited && !$isReplicate ? 'bg-gray-100 text-gray-500' : '' }}"
        {{ $isLimited && !$isReplicate ? 'disabled' : 'required' }}>

        @php
            $labels = [
                1 => 'シングルス（1名）',
                2 => 'ダブルス（2名1組）',
                // 3 => 'トリオス（3名1組）',
                // 4 => 'カルテット（4名1組）',
                // 5 => 'クインテット（5名1組）',
            ];
        @endphp

        @foreach ($labels as $value => $label)
            <option value="{{ $value }}"
                {{ old('max_team_size', $event->max_team_size ?? 1) == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>

    @if ($isLimited && !$isReplicate)
        <input type="hidden" name="max_team_size" value="{{ $event->max_team_size }}">
        <x-slot:hint>
            <span class="text-red-500 font-bold">※公開中のため変更できません</span>
        </x-slot:hint>
    @else
        <x-slot:hint>
            形式に合わせて、ユーザーはチームメイトを招待してエントリーします
        </x-slot:hint>
    @endif
</x-form.group>

{{-- キャンセル待ち設定 --}}
<x-form.group label="キャンセル待ち" help-key="admin.events.allow_waitlist" :is-limited-field="true" type="admin">
    <div class="flex gap-6 py-2">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="allow_waitlist" value="1"
                class="text-admin focus:ring-admin border-gray-300"
                {{ old('allow_waitlist', $event->allow_waitlist ?? 1) == 1 ? 'checked' : '' }}
                {{ $isLimited && !$isReplicate ? 'disabled' : '' }}>
            <span class="text-sm text-gray-700">有（受け付ける）</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="allow_waitlist" value="0"
                class="text-admin focus:ring-admin border-gray-300"
                {{ old('allow_waitlist', $event->allow_waitlist ?? 1) == 0 ? 'checked' : '' }}
                {{ $isLimited && !$isReplicate ? 'disabled' : '' }}>
            <span class="text-sm text-gray-700">無</span>
        </label>
    </div>

    @if ($isLimited && !$isReplicate)
        <input type="hidden" name="allow_waitlist" value="{{ $event->allow_waitlist }}">
        <x-slot:hint><span class="text-red-500 font-bold">※公開中のため変更できません</span></x-slot:hint>
    @else
        <x-slot:hint>定員に達した後、キャンセル待ちエントリーを許可するかどうかを選択します</x-slot:hint>
    @endif
</x-form.group>

{{-- 募集クラス --}}
<x-form.group label="募集クラスの設定" help-key="admin.events.classes" type="admin">
    {{-- 右側に表示する一括操作ボタン --}}
    <x-slot:labelAction>
        <div class="flex gap-3 text-xs">
            <button type="button" onclick="selectAllClasses(true)" class="text-blue-600 hover:underline">すべて選択</button>
            <span class="text-gray-300">|</span>
            <button type="button" onclick="selectAllClasses(false)" class="text-red-600 hover:underline">すべて解除</button>
        </div>
    </x-slot:labelAction>

    <div class="bg-gray-50 p-4 rounded-lg border grid grid-cols-4 gap-2">
        @php
            if (is_array(old('classes'))) {
                $selectedClasses = old('classes');
            } elseif (!empty($existingClasses)) {
                $selectedClasses = $existingClasses;
            } else {
                $selectedClasses = isset($event) ? $event->eventClasses->pluck('class_name')->toArray() : [];
            }
        @endphp

        @foreach (App\Enums\PlayerClass::cases() as $class)
            <label
                class="flex items-center gap-2 cursor-pointer bg-white p-2 border rounded hover:border-admin transition">
                <input type="checkbox" name="classes[]" value="{{ $class->value }}" {{-- ここで色を admin に指定 --}}
                    class="class-checkbox rounded text-admin focus:ring-admin border-gray-300"
                    {{ in_array($class->value, $selectedClasses) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">{{ $class->shortLabel() }}</span>
            </label>
        @endforeach
    </div>

    <x-slot:hint>
        ハンディキャップを設定したクラスを選択してください
    </x-slot:hint>
</x-form.group>

{{-- グループ制限（コミュニティ限定設定） 完成しているが未公開 --}}
{{-- <x-form.group label="公開制限（グループ保有者限定）" help-key="admin.events.groups" type="admin">
    <div class="bg-gray-50 p-4 rounded-lg border">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            @php
                // 現在のイベントに紐付いているグループIDを取得
                $selectedGroups = old('groups', isset($event) ? $event->requiredGroups->pluck('id')->toArray() : []);
                
                // コントローラから渡すのがベストですが、一旦現状のロジックを維持
                $myGroups = \App\Models\Group::where('owner_id', Auth::id())->get();
            @endphp

            @forelse($myGroups as $group)
                <label class="flex items-center gap-3 cursor-pointer bg-white p-3 border rounded hover:border-admin transition group">
                    <input type="checkbox" name="groups[]" value="{{ $group->id }}" 
                        {{ in_array($group->id, $selectedGroups) ? 'checked' : '' }}
                        class="rounded text-admin focus:ring-admin border-gray-300">
                    
                    <div class="flex flex-col leading-tight">
                        <span class="text-sm font-bold text-gray-700 group-hover:text-admin-dark transition">
                            {{ $group->name }}
                        </span>
                        @if ($group->description)
                            <span class="text-[10px] text-gray-500 mt-0.5">
                                {{ $group->description }}
                            </span>
                        @endif
                    </div>
                </label>
            @empty
                <div class="col-span-full py-4 text-center">
                    <p class="text-sm text-gray-400 italic">作成済みのグループがありません。</p>
                    <a href="{{ route('admin.groups.create') }}" class="text-xs text-admin hover:underline mt-2 inline-block">
                        新しいグループを作成する
                    </a>
                </div>
            @endforelse
        </div>
    </div>
    
    <x-slot:hint>
        選択したグループに参加しているユーザーのみ、このイベントを表示・エントリーできます。
    </x-slot:hint>
</x-form.group> --}}

{{-- 追加質問 --}}
<x-form.group label="ユーザーへの追加質問・伝達事項" help-key="admin.events.instruction_label" type="admin">
    <x-textarea-input id="instruction_label" name="instruction_label" rows="1" color-type="admin"
        minHeight="min-h-[45px]" {{-- ★ここに minHeight を渡す --}} class="py-2" {{-- パディングを詰めて高さを抑える --}}
        placeholder="例：所属店舗を入力してください / ご質問・ご要望があればご記入ください">
        {{ old('instruction_label', $event->instruction_label ?? '') }}
    </x-textarea-input>
    <x-slot:hint>質問等がなければ空欄のままにしてください</x-slot:hint>
</x-form.group>

{{-- チケット選択 --}}
<div class="mb-4 bg-blue-50 p-2 rounded-lg border border-blue-200">
    <x-form.group label="使用するチケットの選択" help-key="admin.events.ticket_id" type="admin" class="mb-0">

        @if ($isLimited && !$isReplicate)
            <div class="p-2 bg-white border rounded text-gray-600 shadow-sm">
                {{ $event->ticket->plan->display_name ?? '選択済みチケット' }}
            </div>
            <input type="hidden" name="ticket_id" value="{{ $event->ticket_id }}">
        @else
            <select name="ticket_id" id="ticket_id"
                class="w-full border border-gray-300 rounded-md p-2 transition duration-150 focus:outline-none focus:ring-2 focus:border-admin focus:ring-admin shadow-sm bg-white"
                required>
                <option value="">-- 使用するチケットを選択 --</option>
                @foreach ($availableTickets as $ticket)
                    <option value="{{ $ticket->id }}" data-capacity="{{ $ticket->plan->max_capacity }}"
                        @if (old('ticket_id', $event->ticket_id ?? '') == $ticket->id) selected @endif>
                        {{ $ticket->plan->display_name }} (上限{{ $ticket->plan->max_capacity }}名)
                    </option>
                @endforeach
            </select>
        @endif

    </x-form.group>
</div>
