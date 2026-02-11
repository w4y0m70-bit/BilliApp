<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            ユーザー詳細: {{ $user->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">基本情報</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm text-gray-500">名前</dt>
                        <dd class="text-master font-bold">{{ $user->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">メールアドレス</dt>
                        <dd class="text-master">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">登録日時</dt>
                        <dd class="text-master">{{ $user->created_at->format('Y-m-d H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">イベントエントリー履歴</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">開催日</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">イベント名</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">申込日</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($user->userEntries as $entry)
                            <tr>
                                <td class="px-4 py-2 text-sm">
                                    {{ $entry->event->event_date->format('Y-m-d') }}
                                </td>
                                <td class="px-4 py-2 text-sm font-bold">
                                    {{ $entry->event->title }}
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    @if($entry->status === 'entry')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">エントリー</span>
                                    @elseif($entry->status === 'waitlist')
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">キャンセル待ち</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">{{ $entry->status }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">
                                    {{ $entry->created_at->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    エントリー履歴はありません。
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-start">
                <a href="{{ route('master.users.index') }}" class="text-gray-600 hover:underline">← ユーザー一覧に戻る</a>
            </div>
        </div>
    </div>
</x-master-layout>