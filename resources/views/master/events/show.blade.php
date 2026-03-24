<x-master-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                イベント詳細
            </h2>
            <a href="{{ route('master.events.index') }}" class="text-sm text-gray-600 hover:underline">
                ← 一覧に戻る
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4"> {{-- p-6からp-4へ縮小 --}}
                    <div class="flex justify-between items-center border-b pb-2 mb-4">
                        <h3 class="text-md font-bold text-gray-700 dark:text-gray-300">{{ $event->title }}</h3>
                        <div class="flex gap-2">
                            <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-600 rounded">ID: {{ $event->id }}</span>
                            <span class="text-[10px] px-2 py-0.5 bg-green-100 text-green-700 rounded">作成: {{ $event->created_at->format('Y/m/d') }}</span>
                        </div>
                    </div>

                    {{-- 2列または3列のコンパクトなグリッド --}}
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-2 text-sm">
                        
                        {{-- 各項目：flexで横並びに --}}
                        <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-1">
                            <dt class="text-gray-500">開催日時</dt>
                            <dd class="font-semibold">{{ $event->event_date->format('Y/m/d H:i') }}</dd>
                        </div>

                        <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-1">
                            <dt class="text-gray-500">募集締切</dt>
                            <dd>{{ $event->entry_deadline ? $event->entry_deadline->format('m/d H:i') : '未設定' }}</dd>
                        </div>

                        <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-1">
                            <dt class="text-gray-500">公開日時</dt>
                            <dd>{!! $event->published_at ? $event->published_at->format('m/d H:i') : '<span class="text-red-500">非公開</span>' !!}</dd>
                        </div>

                        <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-1">
                            <dt class="text-gray-500">募集枠 / 1枠人数</dt>
                            <dd>{{ $event->max_entries }} 枠 / {{ $event->max_team_size }} 名</dd>
                        </div>

                        <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-1">
                            <dt class="text-gray-500">総定員 / 補欠</dt>
                            <dd>{{ $event->max_participants }}枠 / {{ $event->allow_waitlist ? '有' : '無' }}</dd>
                        </div>

                        <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-1">
                            <dt class="text-gray-500">管理者</dt>
                            <dd>{{ $event->organizer->name ?? '未設定' }}</dd>
                        </div>

                        <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-1">
                            <dt class="text-gray-500">チケットID</dt>
                            <dd>{{ $event->ticket_id ?? '---' }}</dd>
                        </div>

                        <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-1">
                            <dt class="text-gray-500">質問事項</dt>
                            <dd>{{ $event->instruction_label ?? 'なし' }}</dd>
                        </div>

                        <div class="flex justify-between border-b border-gray-50 dark:border-gray-700 pb-1">
                            <dt class="text-gray-500">状況</dt>
                            <dd class="scale-90 origin-right"><x-event.entry-status :event="$event" /></dd>
                        </div>
                    </dl>

                    {{-- 説明文だけは下に配置 --}}
                    <div class="mt-4 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <dt class="text-xs text-gray-500 mb-1">説明文</dt>
                        <dd class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ $event->description }}</dd>
                    </div>
                </div>
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