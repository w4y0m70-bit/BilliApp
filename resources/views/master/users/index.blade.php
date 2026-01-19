<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            登録ユーザー管理
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">名前</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">メールアドレス</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">登録日</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($users as $user)
                                <tr>
                                    <td class="px-6 py-4 text-sm">{{ $user->id }}</td>
                                    <td class="px-6 py-4 text-sm font-bold">{{ $user->name }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $user->email }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $user->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 text-sm text-right">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('master.users.show', $user) }}" class="text-blue-600 hover:underline">詳細</a>
                                            <form action="{{ route('master.users.destroy', $user) }}" method="POST" onsubmit="return confirm('このユーザーを削除しますか？');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">削除</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>