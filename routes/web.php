<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use App\Services\LineService;

// トップページ
Route::get('/', function () {
    return view('welcome'); // TOPページ
})->name('top');

// スコアボード
Route::view('/scoreboard', 'scoreboard.index')->name('scoreboard');

// LINEメッセージ送信テスト用ルート
Route::get('/line-broadcast-test', function (LineService $lineService) {
    $result = $lineService->broadcast('Laravelからのテスト送信です！成功しました！');
    
    return $result ? 'LINEにメッセージを送りました！確認してください。' : '送信に失敗しました。ログを確認してください。';
});
Route::get('/line-push-test', function (LineService $lineService) {
    $userId = 'U8e87cb76a5ab6380dc076259970644a7';
    
    $result = $lineService->push($userId, 'これはあなただけに向けた個別メッセージです！');
    
    return $result ? '個別メッセージを送信しました！' : '失敗しました。';
});

/*デバッグ用ヘルプ表示ページ*/
Route::get('/_debug/help', function () {
    abort_unless(App::environment('local'), 404);

    $helps = config('help');

    return view('debug.help', compact('helps'));
});