@props([
    'name',
    'label',
    'checked' => false,
    'disabled' => false,
    'value' => '1',
    'reason' => null {{-- 補足メッセージ用に追加 --}}
])

<label class="inline-flex items-center {{ $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer group' }}">
    <input type="checkbox" 
        name="{{ $name }}" 
        value="{{ $value }}"
        @checked(old($name, $checked))
        {{ $disabled ? 'disabled' : '' }}
        {!! $attributes->merge([
            'class' => 'rounded border-gray-300 text-user shadow-sm focus:ring-user ' . ($disabled ? 'bg-gray-100' : '')
        ]) !!}
    >
    <span class="ml-2 text-sm text-gray-600">
        {{ $label }}
        @if($reason)
            <span class="text-[10px] text-gray-400 block md:inline md:ml-1">{{ $reason }}</span>
        @endif
    </span>
</label>