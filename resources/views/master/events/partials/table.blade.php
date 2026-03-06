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
                            <div class="flex items-center gap-1">
                                {{-- 現在の確定チーム数 --}}
                                <span class="font-bold text-gray-900 dark:text-gray-100">
                                    {{ $event->entry_count }}
                                </span>
                                <span class="text-gray-400">/</span>
                                {{-- 最大チーム数 --}}
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ $event->max_entries }}
                                </span>
                                <span class="text-[10px] text-gray-500 ml-1">
                                    {{ $event->max_team_size == 2 ? 'ペア' : '名' }}
                                </span>
                            </div>
                            
                            {{-- キャンセル待ちがある場合のみバッジを表示 --}}
                            @if($event->waitlist_count > 0)
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-orange-100 text-orange-800">
                                        WL: {{ $event->waitlist_count }}
                                    </span>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>