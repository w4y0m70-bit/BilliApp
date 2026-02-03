<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ダッシュボード') }}
        </h2>
    </x-slot>

    <div class="px-6 py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <a href="{{ route('master.admins.index') }}" class="block p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-transparent hover:border-blue-500 transition group">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">登録管理者数</div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $admins -> count() }}名</div>
                        <div class="text-blue-500 group-hover:translate-x-1 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('master.users.index') }}" class="block p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-transparent hover:border-blue-500 transition group">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">登録ユーザー数</div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $userCount }}名</div>
                        <div class="text-blue-500 group-hover:translate-x-1 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('master.tickets.index') }}" class="block p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-transparent hover:border-blue-500 transition group">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">キャンペーンコード発行数</div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $ticketCount }}件</div>
                        <div class="text-blue-500 group-hover:translate-x-1 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                </a>
                
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow sm text-gray-400">
                    <p class="text-sm uppercase">本日の発行チケット数</p>
                    <p class="text-3xl font-bold">-- 件</p>
                </div>

                <!-- <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow sm text-gray-400">
                    <p class="text-sm uppercase">システム稼働状況</p>
                    <p class="text-3xl font-bold text-green-500">Normal</p>
                </div> -->
            </div>
        </div>
    </div>
    
</x-master-layout>