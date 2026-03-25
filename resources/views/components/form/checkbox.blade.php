@props([
    'name',
    'label',
    'checked' => false,
    'disabled' => false,
    'value' => '1',
    'reason' => null,
    'type' => 'user', // デフォルトは user
])

@php
    // タイプに応じたカラークラスの定義
    $colorClasses = match ($type) {
        'admin' => 'text-admin focus:ring-admin',
        'user' => 'text-user focus:ring-user',
        'green' => 'text-green-600 focus:ring-green-500', // LINE用など
        default => 'text-blue-600 focus:ring-blue-500',
    };
@endphp

<label class="inline-flex items-center {{ $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer group' }}">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked(old($name, $checked))
        {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
            'class' =>
                "rounded border-gray-300 shadow-sm focus:ring-offset-0 $colorClasses " . ($disabled ? 'bg-gray-100' : ''),
        ]) !!}>
    <span class="ml-2 text-sm text-gray-700 font-medium group-hover:text-gray-900 transition-colors">
        {{ $label }}
        @if ($reason)
            <span class="text-[10px] text-gray-400 block md:inline md:ml-1 font-normal">{{ $reason }}</span>
        @endif
    </span>
</label>
