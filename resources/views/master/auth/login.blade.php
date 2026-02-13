<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-indigo-700">System Master</h2>
                <p class="text-sm text-gray-600">システムマスター専用ログイン</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('master.login.post') }}">
                @csrf

                <div>
                    <x-input-label for="admin_id" value="マスターID" />
                    <x-text-input id="admin_id" class="block mt-1 w-full" type="text" name="admin_id" :value="old('admin_id')" required autofocus />
                    <x-input-error :messages="$errors->get('admin_id')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password" value="パスワード" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                        <span class="ms-2 text-sm text-gray-600">ログイン状態を保持する</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <a href="{{ route('admin.login') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                        一般管理者はこちら
                    </a>
                    <x-primary-button class="ms-3 bg-indigo-700 hover:bg-indigo-800">
                        マスターログイン
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>