<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;


//毎分期限切れキャンセル待ち処理を実行
Schedule::command('userentry:cancel-expired')->everyMinute();
//1日1回行う処理
// Schedule::command('xxxx:xxxxx')->daily();