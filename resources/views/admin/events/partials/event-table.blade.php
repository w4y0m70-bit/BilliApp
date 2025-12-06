<div x-data="{ openModal: null }" class="flex flex-wrap -mx-2">
    @foreach ($events as $event)
        <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 px-2 mb-4">
            <!-- カード -->
            <div class="bg-white shadow rounded-lg p-4 border cursor-pointer"
                 @click="openModal = {{ $event->id }}">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="text-lg font-semibold">{{ $event->title }}</h4>
                    <a href="{{ route('admin.events.edit', $event) }}"
                       @click.stop
                       class="text-gray-600 hover:text-gray-800">
                        ✏️
                    </a>
                </div>
                <div class="text-sm text-gray-700 mb-1">
                    開催日：{{ $event->event_date->format('Y-m-d H:i') }}
                </div>
                <div class="text-sm text-gray-700 mb-2">
                    締　切：{{ $event->entry_deadline->format('Y-m-d H:i') }}
                </div>
                <div class="text-sm">
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
                                （待ち：{{ $event->waitlist_count }}）
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
                    <p class="mb-2">開催日：{{ $event->event_date->format('Y-m-d H:i') }}</p>
                    <p class="mb-2">締　切：{{ $event->entry_deadline->format('Y-m-d H:i') }}</p>
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
