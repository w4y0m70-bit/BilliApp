<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">管理者編集: {{ $admin->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-lg">
                <form action="{{ route('master.admins.update', $admin->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium">名前</label>
                        <input type="text" name="name" value="{{ old('name', $admin->name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">メールアドレス</label>
                        <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700">
                    </div>

                    <div class="border-t pt-4 mt-4">
                        <p class="text-xs text-gray-500 mb-2">※パスワードを変更する場合のみ入力してください</p>
                        <label class="block text-sm font-medium">新しいパスワード</label>
                        <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">パスワード（確認）</label>
                        <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700">
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                            更新する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-master-layout>