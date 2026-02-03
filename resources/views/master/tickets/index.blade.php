<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            チケット発行・管理
        </h2>
    </x-slot>

    <div class="py-4 px-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div x-data="{ codeType: 'auto' }" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold mb-4">新規キャンペーンコード発行</h3>
                
                <form action="{{ route('master.tickets.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">コード生成方式</label>
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="code_type" value="auto" x-model="codeType" class="text-master">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">自動生成</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="code_type" value="manual" x-model="codeType" class="text-master">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">フリーワード入力</span>
                                    </label>
                                </div>
                            </div>

                            <div x-show="codeType === 'manual'" x-cloak>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">カスタムコード名</label>
                                <input type="text" name="manual_code" placeholder="例: CAMPAIGN2024" 
                                    class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                                @error('manual_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">対象プラン</label>
                                <select name="plan_id" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">コード利用上限回数</label>
                                <input type="number" name="usage_limit" value="1" min="1" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                                <p class="text-xs text-gray-500 mt-1">このコードが全体で何回まで利用可能か設定します。</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">チケット有効日数</label>
                                <input type="number" name="expiry_days" value="40" min="1" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">1コードあたりの付与枚数</label>
                                <input type="number" name="issue_count" value="1" min="1" class="mt-1 block w-full border-gray-300 dark:bg-gray-700 dark:text-white rounded-md shadow-sm">
                                <p class="text-xs text-gray-500 mt-1">ユーザーがコードを入力した際、何枚のチケットを付与するか設定します。</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">コード入力期限</label>
                                <input type="date" name="valid_until" 
                                    value="{{ now()->addMonths(1)->format('Y-m-d') }}" 
                                    class="w-full border p-2 rounded" required>
                                <p class="text-xs text-gray-500 mt-1">※設定した日付までこのコードが使用できます。</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="bg-master hover:bg-master-dark text-white font-bold py-2 px-6 rounded-md shadow transition">
                            この内容で発行する
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">発行済みコード履歴</h3>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">コード</th>
                            <th class="px-4 py-2 text-left">入力期限</th>
                            <th class="px-4 py-2 text-left">プラン</th>
                            <th class="px-4 py-2 text-left">利用 / 上限</th>
                            <th class="px-4 py-2 text-left">有効日数</th>
                            <th class="px-4 py-2 text-left">削除</th> </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($codes as $code)
                        <tr>
                            <td class="px-4 py-2 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $code->code }}</td>
                            <td class="px-4 py-2">{{ $code->valid_until->format('Y/m/d') }}</td>
                            <td class="px-4 py-2">{{ $code->plan?->display_name }} ✕ {{ $code->issue_count }} 枚</td>
                            <td class="px-4 py-2">{{ $code->used_count }} / {{ $code->usage_limit }}</td>
                            <td class="px-4 py-2">{{ $code->expiry_days }}日</td>
                            <td class="px-4 py-2">
                                <form action="{{ route('master.tickets.destroy', $code->id) }}" method="POST" onsubmit="return confirm('本当にこのコードを削除しますか？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold">
                                        ✕
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