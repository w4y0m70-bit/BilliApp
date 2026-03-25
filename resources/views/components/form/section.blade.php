@props(['title', 'type' => 'user', 'maxWidth' => 'max-w-4xl', 'errors' => null])

@php
    $themes = [
        'user' => ['btn' => 'bg-user hover:bg-user-dark', 'text' => 'text-user'],
        'admin' => ['btn' => 'bg-admin hover:bg-admin-dark', 'text' => 'text-admin'],
    ];
    $theme = $themes[$type] ?? $themes['user'];
@endphp

<div class="py-2 px-4 w-full">
    {{-- タイトルとバリデーションをメインコンテンツと同じ幅のコンテナに入れる --}}
    <div class="{{ $maxWidth }} mx-auto">
        {{-- タイトル --}}
        <h2 class="text-2xl font-bold mb-3 {{ $theme['text'] }}">{{ $title }}</h2>

        {{-- バリデーションエラー表示 --}}
        @if ($errors && $errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 shadow-sm">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- メインコンテンツ --}}
    <div class="bg-white p-4 rounded-lg shadow-md border border-gray-100 w-full {{ $maxWidth }} mx-auto">
        {{ $slot }}
    </div>
</div>
