<div x-data="{ openModal: null }" class="flex flex-wrap -mx-2">
    @foreach ($events as $event)
        <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 px-2 mb-2">
            <!-- カード -->
            <div class="bg-white shadow rounded-lg p-4 border cursor-pointer"
                 @click="openModal = {{ $event->id }}">
                <div class="flex justify-between items-start mb-1">
                    <h4 class="text-lg font-semibold text-admin">{{ $event->title }}</h4>
                    <div class="flex justify-between items-start">
                        {{-- 操作アイコン（編集 or 複製 または なし） --}}
                        @php
                            $isPast = $event->event_date->lt(now());
                            $isPublished = $event->published_at && $event->published_at->lte(now());
                        @endphp

                        {{-- ① 過去イベントは最優先で複製アイコン --}}
                        @if ($isPast)
                            <a href="{{ route('admin.events.replicate', $event) }}"
                            @click.stop
                            class="text-gray-700 hover:text-blue-700"
                            title="このイベントを元に新規作成">
                                <span class="material-symbols-outlined">content_copy</span>
                            </a>

                        {{-- ② それ以外 → 編集アイコン --}}
                        @else
                            <a href="{{ route('admin.events.edit', $event) }}"
                            @click.stop
                            class="text-gray-700 hover:text-blue-700">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                        @endif
                    </div>
                </div>
                <!-- グループ -->
                @if($event->requiredGroups->isNotEmpty())
                    <div class="flex flex-wrap gap-1 mb-2">
                        @foreach($event->requiredGroups as $group)
                            <span class="inline-flex items-center text-xs px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100 font-bold">
                                {{ $group->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
                <!-- クラス -->
                 <div class="mb-2 text-xs">
                    <div class="flex flex-wrap gap-2">
                        @forelse($event->eventClasses as $class)
                            <span class="bg-white border px-2 py-1 rounded shadow-sm">{{ $class->class_name }}</span>
                        @empty
                            <span class="text-red-500">クラス設定なし</span>
                        @endforelse
                    </div>
                </div>
                <div class="text-sm text-gray-700 mb-1">
                    開催日：
                    {{ $event->event_date->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}
                </div>

                <div class="text-sm text-gray-700 mb-1">
                    締　切：
                    {{ $event->entry_deadline->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}
                </div>

                {{-- 未公開イベントのみ公開日時を表示 --}}
                @if(!$isPublished && $event->published_at)
                    <div class="text-sm text-red-600 mt-1">
                        公開日：
                        {{ $event->published_at->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}
                    </div>
                @endif


                <div class="text-sm mt-2">
                    参加数：
                    @if ($isPast)
                        {{ $event->entry_count }} / {{ $event->max_participants }}
                        @if ($event->waitlist_count > 0)
                            <span>
                                （WL：{{ $event->waitlist_count }}）
                            </span>
                        @endif
                    @else
                        <a 
                            href="{{ route('admin.events.participants.index', $event->id) }}" 
                            class="text-blue-600 underline hover:text-blue-800"
                        >
                            {{ $event->entry_count }} / {{ $event->max_participants }}
                            @if ($event->waitlist_count > 0)
                                <span>
                                    （WL：{{ $event->waitlist_count }}）
                                </span>
                            @endif
                        </a>
                    @endif
                </div>

            </div>

            <!-- モーダル -->
            <div x-show="openModal === {{ $event->id }}"
                x-transition
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
                x-cloak>
                <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative max-h-[80vh] overflow-y-auto">
                <button class="absolute top-2 right-2 text-gray-600 text-2xl" @click="openModal = null">&times;</button>
                
                <h3 class="text-xl font-semibold mb-4 border-b pb-2">{{ $event->title }}</h3>
                
                <div class="grid grid-cols-1 gap-2 mb-4 text-sm">
                    <p><span class="font-bold w-20 inline-block">開催日：</span>{{ $event->event_date->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}</p>
                    <p><span class="font-bold w-20 inline-block">締　切：</span>{{ $event->entry_deadline->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}</p>
                    <p><span class="font-bold w-20 inline-block">公開日：</span>{{ $event->published_at ? $event->published_at->isoFormat('YYYY/MM/DD（ddd）HH:mm') : '未設定' }}</p>
                </div>

                <div class="bg-blue-50 p-3 rounded mb-4 text-sm">
                    <p class="font-bold text-blue-800 mb-1">【使用チケット】</p>
                    @if($event->ticket && $event->ticket->plan)
                        <p>{{ $event->ticket->plan->display_name }} (ID: {{ $event->ticket_id }})</p>
                        <p class="text-xs text-gray-500">上限：{{ $event->ticket->plan->max_capacity }}名</p>
                    @else
                        <p class="text-red-500">チケットが紐付いていません</p>
                    @endif
                </div>

                <div class="bg-gray-50 p-3 rounded mb-4 text-sm">
                    <p class="font-bold mb-1 text-gray-800">【募集クラス】</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse($event->eventClasses as $class)
                            <span class="bg-white border px-2 py-1 rounded shadow-sm">{{ $class->class_name }}</span>
                        @empty
                            <span class="text-red-500">クラス設定なし</span>
                        @endforelse
                    </div>
                </div>

                <div class="bg-blue-50 p-3 rounded mb-4 text-sm border border-blue-100">
                    <p class="font-bold mb-1 text-blue-800 flex items-center">
                        【参加可能なグループ］
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @forelse($event->requiredGroups as $group)
                            <div class="bg-white px-2 py-1 rounded shadow-sm border border-blue-200">
                                <span class="font-bold text-blue-700">{{ $group->name }}</span>
                                <span class="text-[10px] text-gray-500 ml-1">({{ $group->rank_name }})</span>
                            </div>
                        @empty
                            <span class="text-gray-500 italic">制限なし（誰でも参加可能）</span>
                        @endforelse
                    </div>
                </div>

    @if($event->instruction_label)
        <div class="mb-4 text-sm">
            <p class="font-bold text-gray-800">【追加質問項目】</p>
            <p class="p-2 bg-yellow-50 rounded border border-yellow-100">{{ $event->instruction_label }}</p>
        </div>
    @endif

    <div class="text-sm text-gray-700 space-y-2 break-words border-t pt-4">
        <p class="font-bold">【イベント詳細説明】</p>
        <div class="p-2 bg-gray-50 rounded whitespace-pre-wrap">{!! e($event->description) !!}</div>
    </div>

    <div class="mt-6 text-right">
        <button class="px-6 py-2 bg-gray-600 text-white rounded hover:bg-gray-700" @click="openModal = null">
            閉じる
        </button>
    </div>
</div>
            </div>

        </div>
    @endforeach
</div>
