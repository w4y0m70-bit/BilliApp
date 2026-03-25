@props([
    'size' => 'md',
    'bgColor' => 'bg-gray-500',
])

@php
    $sizeClass =
        [
            'sm' => 'text-[9px] px-1 py-0.5',
            'md' => 'text-[12px] px-2 py-1',
        ][$size] ?? 'text-xs px-2 py-0.5';
@endphp

<span
    class="inline-flex items-center justify-center font-bold rounded text-white leading-none shadow-sm {{ $sizeClass }} {{ $bgColor }}">
    {{ $slot }}
</span>
