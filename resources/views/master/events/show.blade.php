<x-master-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                イベント詳細: {{ $event->title }}
            </h2>
            <a href="{{ route('master.events.index') }}" class="text-sm text-gray-600 hover:underline">
                ← 一覧に戻る
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <div class="flex justify-between border-b pb-4 mb-4">
                    <h3 class="text-lg font-medium">イベント概要</h3>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs self-center">
                        ID: {{ $event->id }}
                    </span>
                </div>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500">開催日時</dt>
                        <dd class="text-base font-bold">{{ $event->event_date->format('Y-m-d H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">イベント管理者</dt>
                        <dd class="text-base">{{ $event->organizer->name ?? '未設定' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">参加状況</dt>
                        <dd class="text-base">
                            <span class="text-xl font-bold">{{ $event->entry_count }}</span> / {{ $event->max_participants }} 名
                            @if($event->waitlist_count > 0)
                                <span class="text-orange-500 text-sm ml-2">(キャンセル待ち: {{ $event->waitlist_count }}名)</span>
                            @endif
                        </dd>
                    </div>
                    <div class="md:col-span-3">
                        <dt class="text-sm text-gray-500">説明文</dt>
                        <dd class="text-sm mt-1 text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $event->description }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">参加者・エントリー名簿</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                {{-- 幅を最小限に --}}
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-16">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-20 text-center">クラス</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">名前</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">申込日時</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($event->userEntries as $entry)
                            <tr class="{{ $entry->status === 'waitlist' ? 'bg-orange-50/50 dark:bg-orange-900/10' : '' }}">
                                {{-- 1. エントリー順番号 or WL順位 --}}
                                <td class="px-4 py-4 text-sm font-mono">
                                    @if($entry->status === 'entry')
                                        <span class="text-gray-400">#</span>{{ $loop->iteration }}
                                    @else
                                        <span class="text-orange-600 font-bold">WL{{ $entry->waitlist_position }}</span>
                                    @endif
                                </td>

                                {{-- 2. クラス表示（Enumの短縮形を使用） --}}
                                <td class="px-4 py-4 text-sm text-center">
                                    <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-[10px] font-bold border border-gray-200 dark:border-gray-600">
                                        {{-- 
                                            Enumに shortLabel() や value など、短縮形を返すメソッドがある想定です。
                                            例: $entry->class->shortLabel() もしくは $entry->class->value
                                        --}}
                                        {{ method_exists($entry->class, 'shortLabel') ? $entry->class->shortLabel() : $entry->class->value }}
                                    </span>
                                </td>

                                {{-- 3. 名前（ゲストは青色） --}}
                                <td class="px-4 py-4 text-sm font-bold">
                                    @if(!$entry->user_id)
                                        <span class="text-blue-600 dark:text-blue-400">{{ $entry->name }}</span>
                                    @else
                                        <span class="text-gray-900 dark:text-white">{{ $entry->name ?: $entry->user->name }}</span>
                                    @endif
                                </td>

                                {{-- 4. 申込日時（秒を省いてスリムに） --}}
                                <td class="px-4 py-4 text-sm text-gray-500 whitespace-nowrap">
                                    {{ $entry->created_at->format('m/d H:i') }}
                                </td>

                                {{-- 5. 操作 --}}
                                <td class="px-4 py-4 text-sm text-right whitespace-nowrap">
                                    @if($entry->user_id && $entry->user->id !== null)
                                        <a href="{{ route('master.users.show', $entry->user_id) }}" class="text-indigo-600 hover:underline">詳細</a>
                                    @else
                                        <span class="text-gray-400 text-[10px]">GUEST</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                    エントリーはありません。
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                <h3 class="text-red-700 dark:text-red-400 text-lg font-medium mb-2">管理アクション</h3>
                <p class="text-sm text-red-600 dark:text-red-400 mb-4">このイベントを強制的に削除します。この操作は取り消せません。</p>
                <form action="{{ route('master.events.destroy', $event) }}" method="POST" onsubmit="return confirm('本当にこのイベントを削除しますか？');">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow text-sm font-bold">
                        イベントを強制削除する
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-master-layout>