<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LineWebhookController;

Route::post('/line/webhook', LineWebhookController::class);
Route::post('/line/webhook', function (Request $request) {
    // LINEから届いたデータ（イベント）を取得
    $events = $request->input('events');

    foreach ($events as $event) {
        // 「友だち追加（follow）」イベントの場合
        if ($event['type'] === 'follow') {
            $userId = $event['source']['userId'];
            Log::info("新しい友だちが追加されました！ユーザーID: " . $userId);
            
            // ここでデータベースに保存する処理（例: User::create(...)）を書きます
        }
    }

    return response('OK', 200);
});