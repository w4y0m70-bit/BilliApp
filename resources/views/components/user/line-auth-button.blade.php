@props([
    'type' => 'login', // login or register or link
])

@php
    $text = match($type) {
        'login' => 'LINEでログイン',
        'register' => 'LINEで新規登録',
        'link' => 'LINEと連携する',
        default => 'LINEでログイン',
    };
@endphp

<a href="{{ route('user.line.login') }}" 
   {{ $attributes->merge(['class' => 'w-full inline-flex justify-center items-center bg-[#06C755] hover:bg-[#05b34c] text-white font-bold py-2 px-4 rounded transition shadow-sm']) }}>
    <img src="{{ asset('images/LINE_Brand_icon.png') }}" 
         alt="LINEアイコン" 
         class="w-6 h-6 mr-2 object-contain">
    {{ $text }}
</a>