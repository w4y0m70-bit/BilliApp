<x-master-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('システムマスター・ダッシュボード') }}
        </h2>
    </x-slot>

    <div class="px-6 py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow sm">
                    <p class="text-sm text-gray-500 uppercase">登録管理者数</p>
                    <p class="text-3xl font-bold">{{ $admins->count() }} 名</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow sm">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        登録ユーザー数
                    </div>
                    <div class="mt-2 flex items-baseline">
                        <div class="text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($userCount) }}
                        </div>
                        <div class="ml-2 text-sm text-gray-500">
                            人
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow sm">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                        キャンペーンコード発行数
                    </div>
                    <div class="mt-2 flex items-baseline">
                        <div class="text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($ticketCount) }}
                        </div>
                        <div class="ml-2 text-sm text-gray-500">
                            件
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow sm text-gray-400">
                    <p class="text-sm uppercase">本日の発行チケット数</p>
                    <p class="text-3xl font-bold">-- 件</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow sm text-gray-400">
                    <p class="text-sm uppercase">システム稼働状況</p>
                    <p class="text-3xl font-bold text-green-500">Normal</p>
                </div>
            </div>

            <!-- <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">クイックリンク</h3>
                    <ul class="list-disc ml-5 space-y-2">
                        <li><a href="{{ route('master.admins.index') }}" class="text-blue-500 hover:underline">管理者アカウントの管理</a></li>
                        <li><a href="{{ route('master.tickets.index') }}" class="text-blue-500 hover:underline">チケットコードの新規発行</a></li>
                    </ul>
                </div>
            </div> -->
        </div>
    </div>
    
</x-master-layout>