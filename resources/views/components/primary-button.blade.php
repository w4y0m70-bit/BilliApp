@props(['type' => 'user'])

@php
// Tailwind設定に基づいたクラスの割り当て
$colors = [
    'user'   => 'bg-user hover:bg-user-dark focus:ring-user-light',
    'admin'  => 'bg-admin hover:bg-admin-dark focus:ring-admin-light',
    'master' => 'bg-master hover:bg-master-dark focus:ring-master-light',
    'base'   => 'bg-base hover:bg-base-dark focus:ring-base-light',
];

$selectedColor = $colors[$type] ?? $colors['user'];
@endphp

<button {{ $attributes->merge([
    'type' => 'submit', 
    'class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 ' . $selectedColor
]) }}>
    {{ $slot }}
</button>