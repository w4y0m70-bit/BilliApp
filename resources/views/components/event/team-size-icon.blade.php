@props([
    'size',
    'showLabel' => true, // ラベルを表示するかどうか
    'compact' => false,
])

@php
    $size = (int) $size;

    // モデルを経由せず、Enumを直接呼び出します（これなら静的に呼べます）
    $teamType = \App\Enums\TeamType::fromSize($size);
    $teamTypeName = $teamType->label(); // 'シングルス' などの文字列を取得

    // 人数に応じた色設定
    // ※ Enumの colorClass() を使わずに、ここで独自に設定されている場合はそのままでOK
    $colors = match ($size) {
        1 => 'bg-gray-100 text-gray-600 border-gray-200',
        2 => 'bg-blue-50 text-blue-700 border-blue-100',
        3 => 'bg-green-50 text-green-700 border-green-100',
        default => 'bg-purple-50 text-purple-700 border-purple-100',
    };

    // サイズ設定
    $containerClass = $compact ? 'px-1 py-0.5' : 'px-2 py-1';
    $iconHeight = $compact ? 'h-3.5' : 'h-5';
    $textSize = $compact ? 'text-[9px]' : 'text-[10px]';
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-md border shadow-sm $colors $containerClass"]) }}
    title="{{ $teamTypeName }}">

    <div class="flex items-center">
        @if ($size <= 3)
            {{-- 自作SVGコンポーネントに高さを渡す --}}
            <x-event.icon-player :size="$size" class="{{ $iconHeight }} w-auto" />
        @else
            <x-event.icon-player :size="1" class="{{ $iconHeight }} w-auto" />
            <span class="{{ $textSize }} font-bold ml-0.5 mt-0.5">×{{ $size }}</span>
        @endif
    </div>

    @if ($showLabel)
        <span class="{{ $textSize }} font-bold ml-1 border-l opacity-70 pl-1 border-current leading-none py-0.5">
            {{ $teamTypeName }}
        </span>
    @endif
</span>
