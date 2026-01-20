<div class="p-6">
    @if($events->isEmpty())
        <p class="text-center text-gray-500 py-10">表示するイベントはありません。</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">開催日</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">イベント</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">管理者</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">参加状況</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($events as $event)
                    <tr class="{{ $type === 'past' ? 'opacity-75' : '' }}">
                        <td class="px-4 py-4 text-sm">
                            <span class="{{ $type === 'upcoming' ? 'font-bold' : '' }}">
                                {{ $event->event_date->format('Y/m/d H:i') }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm font-bold">{{ $event->title }}</td>
                        <td class="px-4 py-4 text-sm">{{ $event->organizer->name ?? '---' }}</td>
                        <td class="px-4 py-4 text-sm">
                            {{ $event->entry_count }} / {{ $event->max_participants }}
                        </td>
                        <td class="px-4 py-4 text-sm text-right">
                            <a href="{{ route('master.events.show', $event) }}" class="text-blue-600 hover:underline">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>