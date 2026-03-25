@props(['message'])
<div
    {{ $attributes->merge(['class' => 'bg-gray-50 border border-dashed border-gray-300 text-gray-500 p-8 text-center rounded-lg']) }}>
    {{ $message }}
</div>
