<nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="shrink-0 flex items-center mr-8">
                    <a href="{{ route('master.dashboard') }}" class="font-bold text-xl dark:text-white">
                        Master Panel
                    </a>
                </div>

                <div class="flex space-x-8 ml-10">
                    <a href="{{ route('master.dashboard') }}" 
                       class="text-sm font-medium leading-5 {{ request()->routeIs('master.dashboard') ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }} transition">
                        ダッシュボード
                    </a>

                    <a href="{{ route('master.admins.index') }}" 
                       class="text-sm font-medium leading-5 {{ request()->routeIs('master.admins.*') ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }} transition">
                        管理者一覧
                    </a>

                    <a href="{{ route('master.tickets.index') }}" 
                       class="text-sm font-medium leading-5 {{ request()->routeIs('master.tickets.*') ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400' }} transition">
                        チケット発行
                    </a>
                </div>
            </div>

            <div class="flex items-center">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        ログアウト
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>