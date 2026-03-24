@props([
    'disabled' => false,
    'colorType' => 'user',
    'minHeight' => '',
])

@php
    $focusColors = [
        'user' => 'focus:border-user focus:ring-user',
        'admin' => 'focus:border-admin focus:ring-admin',
        'master' => 'focus:border-master focus:ring-master',
    ];
    // colorType プロパティを見て色を決定
    $selectedFocus = $focusColors[$colorType] ?? $focusColors['user'];
@endphp

<textarea {{ $disabled ? 'disabled' : '' }} style="resize: vertical;" {!! $attributes->merge([
    'class' =>
        'w-full border border-gray-300 rounded-md shadow-sm p-3 transition duration-150 focus:outline-none focus:ring-2 ' .
        $minHeight .
        ' ' .
        $selectedFocus,
]) !!}>{{ $slot }}</textarea>
