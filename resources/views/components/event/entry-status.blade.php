@props([
    'event',
    'href' => null,
])

@php
    $max = $event->max_entries;

    // キャンセルされていない有効な全エントリーを取得
    $allActiveEntries = $event->userEntries()
        ->where('status', '!=', 'cancelled');

    // 1. 定員内のカウント（order が 1 〜 max）
    $entryCount = (clone $allActiveEntries)
        ->where('order', '<=', $max)
        ->count();

    // 2. 定員外のカウント（order が max より大きい）
    $waitlistCount = (clone $allActiveEntries)
        ->where('order', '>', $max)
        ->count();

    $unit = $event->max_team_size >= 2 ? 'チーム' : '名';
@endphp

<span {{ $attributes->merge(['class' => 'inline-block']) }}>
    @if ($href)
        <a href="{{ $href }}" @click.stop class="text-blue-600 hover:underline font-bold">
            {{ $entryCount }} ／ {{ $max }}{{ $unit }}
            <span class="text-sm font-normal ml-1 text-gray-500">
                （
                @if($event->allow_waitlist)
                    WL：{{ $waitlistCount }}
                @else
                    <span class="text-gray-400">✕</span>
                @endif
                ）
            </span>
        </a>
    @else
        <span class="font-bold text-gray-800">
            {{-- ここも $max を使用 --}}
            {{ $entryCount }} ／ {{ $max }}{{ $unit }}
            <span class="text-sm font-normal text-gray-500 ml-1">
                （WL：{{ $event->allow_waitlist ? $waitlistCount : '✕' }}）
            </span>
        </span>
    @endif
</span>