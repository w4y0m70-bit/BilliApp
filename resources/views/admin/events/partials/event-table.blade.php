<table class="w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2 text-left">開催日</th>
            <th class="border p-2 text-left">イベント名</th>
            <th class="border p-2 text-left">参加人数（キャンセル待ち）</th>
            <th class="border p-2 text-left">エントリー締切</th>
            <th class="border p-2 text-left">
                @if(!($hideActions ?? false))
                    編集
                @else
                    操作
                @endif
            </th>
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
                @if(!($hideActions ?? false))
                    {{-- 編集ボタン --}}
                    <a href="{{ route('admin.events.edit', $event->id) }}" class="text-gray-600 hover:text-gray-900">
                        編集
                    </a>
                @else
                    {{-- これを元に新規作成 --}}
                    <a href="{{ route('admin.events.replicate', $event->id) }}" class="text-green-600 hover:underline">
                        これを元に新規作成
                    </a>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="p-2 text-center text-gray-500">該当するイベントはありません</td>
        </tr>
        @endforelse
    </tbody>
</table>
