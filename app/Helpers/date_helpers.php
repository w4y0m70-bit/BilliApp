<?php

use Carbon\Carbon;

/**
 * 開催日の曜日を含めたフォーマットを返す
 * 例）2025/11/15（土）
 */
if (!function_exists('format_event_date')) {
    function format_event_date(Carbon|string $date): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $w = $weekdays[$date->dayOfWeek];

        return $date->format('Y/m/d') . "（{$w}）";
    }
}
