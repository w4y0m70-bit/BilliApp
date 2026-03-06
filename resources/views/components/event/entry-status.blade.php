@props([
    'event',
    'href' => null,
])

@php
    // チーム数（UserEntryレコード数）の集計
    // entry だけでなく pending（承認待ち）も枠確保としてカウントに含めるのが安全です
    $entryCount = $event->userEntries()->whereIn('status', ['entry', 'pending'])->count();
    $waitlistCount = $event->userEntries()->where('status', 'waitlist')->count();
    $unit = $event->max_team_size == 2 ? 'チーム' : '名';
@endphp

<span {{ $attributes->merge(['class' => 'inline-block']) }}>
    @if ($href)
        <a href="{{ $href }}" @click.stop class="text-blue-600 hover:underline font-bold">
            {{ $entryCount }} ／ {{ $event->max_entries }}{{ $unit }}
            <span class="text-sm font-normal ml-1">
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
            {{ $entryCount }} ／ {{ $event->max_entries }}{{ $unit }}
            <span class="text-sm font-normal text-gray-500 ml-1">
                （WL：{{ $event->allow_waitlist ? $waitlistCount : '✕' }}）
            </span>
        </span>
    @endif
</span>