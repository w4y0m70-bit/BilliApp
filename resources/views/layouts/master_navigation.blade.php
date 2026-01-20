<nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
<div class="flex items-center">
    <div class="flex space-x-8 ml-10">
        <a href="{{ route('master.dashboard') }}" 
           class="text-sm font-medium leading-5 {{ request()->routeIs('master.dashboard') ? 'text-blue-600 border-b-2 border-blue-400' : 'text-gray-500' }}">
            ダッシュボード
        </a>

        <a href="{{ route('master.tickets.index') }}" 
           class="text-sm font-medium leading-5 {{ request()->routeIs('master.tickets.*') ? 'text-blue-600 border-b-2 border-blue-400' : 'text-gray-500' }}">
            チケット発行
        </a>

        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" 
                    class="inline-flex items-center text-sm font-medium leading-5 transition {{ request()->routeIs('master.admins.*', 'master.plans.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                <span>システム管理</span>
                <svg class="ml-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>

            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-left left-0">
                <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                    
                    <a href="{{ route('master.admins.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                        管理者一覧
                    </a>
                    <a href="{{ route('master.users.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                        ユーザー一覧
                    </a>
                    <a href="{{ route('master.events.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                        イベント一覧
                    </a>
                    <a href="{{ route('master.plans.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                        プラン設定
                    </a>

                </div>
            </div>
        </div>
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