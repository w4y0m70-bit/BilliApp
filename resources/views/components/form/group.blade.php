@props([
    'label', 
    'helpKey' => null, 
    'isLimitedField' => false, 
    'type' => 'user', {{-- デフォルトは user --}}
    'error' => null
])

@php
    // typeに応じた色設定（ラベル用など）
    $labelColors = [
        'user'   => 'text-user-dark',
        'admin'  => 'text-admin-dark',
        'master' => 'text-master-dark',
    ];
    $selectedLabelColor = $labelColors[$type] ?? 'text-gray-700';
@endphp

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <div class="flex items-center mb-1 gap-1">
        <label class="font-medium text-gray-700">
            {{ $label }}
            @if($isLimitedField)
                <span class="text-red-500 ml-0.5" title="公開後変更不可">＊</span>
            @endif
        </label>

        @if($helpKey)
            <x-help :help-key="$helpKey" />
        @endif
        
        @if(isset($labelAction))
            <div class="ml-auto">{{ $labelAction }}</div>
        @endif
    </div>

    {{-- inputなどの入力フィールド --}}
    {{ $slot }}

    {{-- 個別の補足メッセージ（スロットで渡せるようにします） --}}
    @if(isset($hint))
        <div class="mt-1 text-xs text-gray-500">
            {{ $hint }}
        </div>
    @endif

    @if($error)
        <p class="text-xs text-red-500 mt-1">{{ $error }}</p>
    @endif
</div>