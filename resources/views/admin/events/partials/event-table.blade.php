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
                                <span class="material-icons">content_copy</span>
                            </a>

                        {{-- ② それ以外 → 編集アイコン --}}
                        @else
                            <a href="{{ route('admin.events.edit', $event) }}"
                            @click.stop
                            class="text-gray-700 hover:text-blue-700">
                                <span class="material-icons">edit</span>
                            </a>
                        @endif
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
                    <a 
                        href="{{ route('admin.events.participants.index', $event->id) }}" 
                        class="text-blue-600 underline hover:text-blue-800"
                    >
                        {{ $event->entry_count }} / {{ $event->max_participants }}
                        @if ($event->waitlist_count > 0)
                            <span 
                                href="{{ route('admin.events.participants.index', $event->id) }}"
                            >
                                （WL：{{ $event->waitlist_count }}）
                            </span>
                        @endif
                    </a>

                </div>
            </div>

            <!-- モーダル -->
            <div x-show="openModal === {{ $event->id }}"
                x-transition
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
                x-cloak>
                <div class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6 relative max-h-[80vh] overflow-y-auto">
                    <button class="absolute top-2 right-2 text-gray-600" @click="openModal = null">×</button>
                    <h3 class="text-xl font-semibold mb-4">{{ $event->title }}</h3>
                    <p class="mb-2">開催日：{{ $event->event_date->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}</p>
                    <p class="mb-2">締　切：{{ $event->entry_deadline->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}</p>
                    <p class="mb-2">公開日：{{ $event->published_at->isoFormat('YYYY/MM/DD（ddd）HH:mm') }}</p>
                    <p class="mb-4">
                        参加：{{ $event->entry_count }} / {{ $event->max_participants }}
                        @if ($event->waitlist_count > 0)
                            <span class="ml-2 text-red-700">（キャンセル待ち：{{ $event->waitlist_count }}）</span>
                        @endif
                    </p>
                    <div class="text-sm text-gray-700 space-y-2 break-words">
                        <!-- <p>会場：{{ $event->venue }}</p> -->
                        <p>説明：{!! nl2br(e($event->description)) !!}</p>
                    </div>
                    <div class="mt-4 text-right">
                        <button class="px-4 py-2 bg-gray-600 text-white rounded" @click="openModal = null">
                            閉じる
                        </button>
                    </div>
                </div>
            </div>

        </div>
    @endforeach
</div>
