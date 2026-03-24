@props(['disabled' => false, 'colorType' => 'user'])

@php
// 注目！ ここで color-type (colorType) を見て色を決めています
$focusColors = [
    'user'   => 'focus:border-user focus:ring-user',
    'admin'  => 'focus:border-admin focus:ring-admin',
    'master' => 'focus:border-master focus:ring-master',
];
$selectedFocus = $focusColors[$colorType] ?? $focusColors['user'];
@endphp

<input {{ $disabled ? 'disabled' : '' }} 
    {!! $attributes->merge([
        'class' => 'w-full border border-gray-300 rounded-md shadow-sm p-2 transition duration-150 focus:outline-none focus:ring-2 ' . $selectedFocus
    ]) !!}>