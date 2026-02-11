<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LineService;
use Illuminate\Http\Request;

class LineNotificationController extends Controller
{
    protected $lineService;

    public function __construct(LineService $lineService)
    {
        $this->lineService = $lineService;
    }

    /**
     * 指定したユーザーID（DBのID）にLINEを送る
     */
    public function sendLineNotification($userId)
    {
        // 1. DBからユーザーを探す
        $user = User::findOrFail($userId);

        // 2. LINE IDが登録されているかチェック
        if (!$user->line_id) {
            return back()->with('error', 'このユーザーはLINE連携されていません。');
        }

        // 3. LineServiceを使って送信
        $message = "{$user->full_name}様、いつもご利用ありがとうございます！";
        $result = $this->lineService->push($user->line_id, $message);

        return $result ? "送信成功" : "送信失敗";
    }
}