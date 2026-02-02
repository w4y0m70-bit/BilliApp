<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>プレイヤーページ | @yield('title')</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>

<body class="bg-gray-100 min-h-screen font-sans text-gray-900">

    <header class="bg-user text-white p-4 flex flex-wrap md:flex-row justify-between items-center gap-y-3">
        <div class="flex items-center w-full md:w-auto mb-2 md:mb-0">
            <a href="{{ url('/') }}">
                <span class="material-icons">home</span>
            </a>
            <h1 class="text-xl font-bold ml-2">
                {{ Auth::user()->name ?? 'プレイヤー' }} <span class="text-lg p-1">の</span>イベント管理
            </h1>
        </div>

        <nav class="bg-gray-800 text-white px-2 py-2 flex items-center gap-1 rounded shadow w-full md:w-auto overflow-x-auto whitespace-nowrap">

            <a href="{{ route('user.events.index') }}"
            class="flex flex-col items-center min-w-[70px] hover:bg-gray-700 px-2 py-1 rounded transition flex-shrink-0">
                <span class="material-icons text-xl">event</span>
                <span class="text-[10px] mt-0.5">イベント</span>
            </a>

            <a href="{{ route('user.account.show') }}"
            class="flex flex-col items-center min-w-[70px] hover:bg-gray-700 px-2 py-1 rounded transition flex-shrink-0">
                <span class="material-icons text-xl">account_circle</span>
                <span class="text-[10px] mt-0.5">アカウント</span>
            </a>

            <form method="POST" action="{{ route('user.logout') }}" class="ml-auto flex-shrink-0">
                @csrf
                <button type="submit"
                        class="flex flex-col items-center min-w-[70px] hover:bg-red-600 px-2 py-1 rounded transition">
                    <span class="material-icons text-xl">logout</span>
                    <span class="text-[10px] mt-0.5">ログアウト</span>
                </button>
            </form>

        </nav>
    </header>

    <main class="container mx-auto py-6">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white text-center p-3 mt-10">
        <p>© {{ date('Y') }} Billents</p>
    </footer>

</body>
</html>
