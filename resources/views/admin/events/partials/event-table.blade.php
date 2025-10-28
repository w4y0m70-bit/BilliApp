<table class="w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">開催日</th>
            <th class="border p-2 text-left">イベント名</th>
            <th class="border p-2 text-left">参加人数（キャンセル待ち）</th>
            <th class="border p-2 text-left">エントリー締切</th>
            <th class="border p-2 text-center">編集</th>
        </tr>
    </thead>
    <tbody>
        @forelse($events as $event)
        <tr>
            <td class="border p-2">{{ $event->event_date->format('Y-m-d H:i') }}</td>
            <td class="border p-2">{{ $event->title }}</td>
            <td class="border p-2">
                <a href="{{ route('admin.events.participants.index', $event->id) }}" class="text-blue-600 hover:underline">
                    {{ $event->entry_count }}/{{ $event->max_participants }}
                    （{{ $event->allow_waitlist ? ($event->waitlist_count ?: 0) : '-' }}）
                </a>
            </td>
            <td class="border p-2">{{ $event->entry_deadline->format('Y-m-d H:i') }}</td>
            <td class="border p-2 text-center">
                <a href="{{ route('admin.events.edit', $event->id) }}" class="text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5m-4-4l5-5m0 0l-5 5m5-5H13"/>
                    </svg>
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="p-2 text-center text-gray-500">該当するイベントはありません</td>
        </tr>
        @endforelse
    </tbody>
</table>
