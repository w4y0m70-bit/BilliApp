<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $events = $request->input('events', []);

        foreach ($events as $event) {
            // 友だち追加（follow）または ブロック解除（unblock）のイベント
            if ($event['type'] === 'follow') {
                $lineId = $event['source']['userId'];

                // 既に登録されているか確認し、なければ新しく作成（または更新）
                User::updateOrCreate(
                    ['line_id' => $lineId],
                    ['name' => 'LINEユーザー'] // 名前は後で取得も可能です
                );

                Log::info("LINE IDを保存しました: " . $lineId);
            }
        }

        return response('OK', 200);
    }
}