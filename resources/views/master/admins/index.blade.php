<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('管理者アカウント管理') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 flex justify-between">
                        <h3 class="text-lg font-bold">登録済み管理者</h3>
                        </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="text-gray-900 dark:text-gray-100">
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">名前・メールアドレス</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">登録日</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($admins as $admin)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $admin->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $admin->name }} >> {{ $admin->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $admin->created_at->format('Y-m-d') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                                                <a href="{{ route('master.admins.edit', $admin->id) }}" class="text-indigo-600 hover:text-indigo-900">編集</a>

                                                <form action="{{ route('master.admins.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('この管理者を削除しますか？');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>