@props(['name', 'label' => null, 'required' => false, 'value' => ''])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold mb-1 text-gray-700">
            {{ $label }}
            @if($required) <span class="text-red-500 ml-1">*</span> @endif
        </label>
    @endif

    <select name="{{ $name }}" id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {!! $attributes->merge([
            'class' => 'border rounded w-full p-2 focus:ring-2 focus:outline-none transition-all ' . 
            ($errors->has($name) ? 'border-red-500 bg-red-50' : 'border-gray-300 focus:ring-user/20 focus:border-user')
        ]) !!}>
        {{ $slot }}
    </select>

    @error($name)
        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>