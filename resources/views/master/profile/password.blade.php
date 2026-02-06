<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            パスワード変更
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">パスワードの更新</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            アカウントの安全性を保つため、長くてランダムなパスワードを使用してください。
                        </p>
                    </header>

                    @if (session('status') === 'password-updated')
                        <div class="mt-4 p-2 bg-green-100 text-green-700 rounded text-sm font-bold">
                            パスワードを更新しました。
                        </div>
                    @endif

                    <form method="post" action="{{ route('master.password.update') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('put')

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">現在のパスワード</label>
                            <input type="password" name="current_password" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">新しいパスワード</label>
                            <input type="password" name="password" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">新しいパスワード（確認）</label>
                            <input type="password" name="password_confirmation" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md shadow-sm">
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                                保存する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-master-layout>