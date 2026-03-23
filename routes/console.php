<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/*
// 毎分期限切れキャンセル待ち処理を実行
*/
// キャンセル待ち繰り上がり
Schedule::command('userentry:cancel-expired')->everyMinute();
// 
Schedule::command('events:send-notifications')->everyMinute();
// エントリー締切
Schedule::command('events:check-deadlines')->everyMinute();

/*
// 1日1回行う処理
*/
// 毎日深夜に古いログを削除する
Schedule::command('activitylog:clean')->daily();