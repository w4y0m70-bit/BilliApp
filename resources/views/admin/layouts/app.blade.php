<!-- resources/views/admin/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ページ | @yield('title')</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 min-h-screen font-sans text-gray-900">

    <header class="bg-green-700 text-white p-4 flex flex-col md:flex-row justify-between items-center">
        <h1 class="text-xl font-bold mb-2 md:mb-0">🎱 管理者ページ</h1>
        <nav class="bg-gray-800 text-white p-3 flex justify-between w-full md:w-auto">
            <a href="{{ route('admin.home') }}" class="font-semibold hover:underline mr-4">ホーム</a>
            <a href="{{ route('admin.events.create') }}" class="hover:underline mr-4">新規イベント</a>
            <a href="{{ route('admin.account') }}" class="hover:underline">アカウント情報</a>
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
