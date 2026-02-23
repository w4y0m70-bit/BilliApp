@props([
    'name',                {{-- 必須：name属性 --}}
    'label' => null,       {{-- ラベルテキスト（なければ表示しない） --}}
    'type' => 'text',      {{-- text, email, password, tel など --}}
    'value' => '',         {{-- 初期値（DBからの値など） --}}
    'required' => false,   {{-- 必須フラグ --}}
    'placeholder' => '',   {{-- プレイスホルダー --}}
    'readonly' => false,   {{-- 読み取り専用 --}}
    'info' => null         {{-- 「ハイフンなし」などの補足説明 --}}
])

<div {{ $attributes->only('class')->merge(['class' => 'mb-1']) }}>
    @if(isset($label) && trim($label) !== '')
        <label for="{{ $name }}" class="block text-sm font-semibold mb-1 text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1">*</span>
            @endif
        </label>
    @endif

    @if($info)
        <p class="text-xs text-gray-500 mb-1">{{ $info }}</p>
    @endif

    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{-- 追加の属性（classなど）をマージする --}}
        {!! $attributes->merge([
            'class' => 'border rounded w-full p-2 focus:ring-2 focus:outline-none transition-all ' . 
            ($errors->has($name) ? 'border-red-500 bg-red-50 focus:ring-red-200' : 'border-gray-300 focus:ring-admin/20 focus:border-admin') .
            ($readonly ? ' bg-gray-100 cursor-not-allowed text-gray-500' : '')
        ]) !!}
    >

    {{-- バリデーションエラーの自動表示 --}}
    @error($name)
        <p class="text-red-600 text-xs mt-1 flex items-center">
            <span class="material-symbols-outlined text-xs mr-1">error</span>
            {{ $message }}
        </p>
    @enderror
</div>