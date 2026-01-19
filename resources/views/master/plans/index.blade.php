<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">プラン設定管理</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('master.plans.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                            + 新規プラン作成
                        </a>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">表示名 / スラッグ</th>
                            <th class="px-4 py-2 text-left">価格</th>
                            <th class="px-4 py-2 text-left">定員上限</th>
                            <th class="px-4 py-2 text-left">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($plans as $plan)
                        <tr>
                            <td class="px-4 py-2">
                                <div class="font-bold">{{ $plan->display_name }}</div>
                                <div class="text-xs text-gray-500">{{ $plan->slug }}</div>
                            </td>
                            <td class="px-4 py-2">{{ number_format($plan->price) }}円</td>
                            <td class="px-4 py-2">{{ $plan->max_capacity }}人</td>
                            <td class="px-4 py-2 flex items-center gap-4">
                                <a href="{{ route('master.plans.edit', $plan) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">編集</a>

                                <form action="{{ route('master.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('このプランを削除しますか？紐付くデータがある場合はエラーになる可能性があります。');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold">
                                        削除
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-master-layout>