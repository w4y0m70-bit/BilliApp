<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>User</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
{{-- items-center を外して、上下にゆとりを持たせる --}}
<body class="bg-gray-100 min-h-screen">

    {{-- 
        ここで max-w-md や bg-white を固定せず、
        各ページ（register, loginなど）側で幅を決められるようにします 
    --}}
    <main>
        @yield('content')
    </main>

</body>
</html>