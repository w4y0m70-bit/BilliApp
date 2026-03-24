<div class="p-4">
    @if($events->isEmpty())
        <p class="text-center text-gray-500 py-10">表示するイベントはありません。</p>
    @else
        {{-- カードを並べるグリッド。スマホ1列、PCは2〜3列 --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($events as $event)
                <a href="{{ route('master.events.show', $event) }}" 
                   class="block p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm {{ $type === 'past' ? 'opacity-60' : '' }}">
                    
                    {{-- 日付とタイトル --}}
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400">
                            {{ $event->event_date->format('Y/m/d') }} 開催
                        </span>
                    </div>
                    
                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1 line-clamp-1">
                        <span class="text-xs font-medium text-gray-400">{{ $event->organizer->name ?? '未設定' }}</span> {{ $event->title }}
                    </h3>
                </a>
            @endforeach
        </div>
    @endif
</div>