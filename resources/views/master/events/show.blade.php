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
                        <dd class="mt-1">
                            <x-event.entry-status :event="$event" />
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
                {{-- ★ 修正：チーム制対応のリストコンポーネントを使用 --}}
                {{-- ※ 管理者向けの表示として 'mode="master"' などのプロパティを渡すと便利です --}}
                <x-event.participant-list 
                    :event="$event" 
                    :participants="$event->userEntries()->where('status', '!=', 'cancelled')->orderBy('order', 'asc')->get()"
                    :max-entries="$event->max_entries"
                    mode="master" 
                />
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