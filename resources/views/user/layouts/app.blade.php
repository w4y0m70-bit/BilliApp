<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>プレイヤーページ | @yield('title')</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 min-h-screen font-sans text-gray-900">

    <header class="bg-user text-white p-4 flex flex-col md:flex-row justify-between items-center">
        <div class="flex items-center w-full md:w-auto mb-2 md:mb-0">
            <!-- 🏠 トップページへ戻るボタン -->
            <a href="{{ url('/') }}" 
               class="text-white hover:text-yellow-300 transition mr-4"
               title="トップページへ戻る">
                <!-- Heroicons Home アイコン -->
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="h-6 w-6 inline-block align-middle" 
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
                </svg>
            </a>

            <h1 class="text-xl font-bold">🎱 プレイヤーページ</h1>
        </div>

        <nav class="bg-gray-800 text-white p-3 flex justify-between w-full md:w-auto rounded">
            <a href="{{ route('user.events.index') }}" class="font-semibold hover:underline mr-4">イベント一覧</a>
            <a href="{{ route('user.account.show') }}" class="hover:underline">アカウント情報</a>
        </nav>
    </header>

    <main class="container mx-auto py-6">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white text-center p-3 mt-10">
        <p>© {{ date('Y') }} Billiard Entry System</p>
    </footer>

</body>
</html>
