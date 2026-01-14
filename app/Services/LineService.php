<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineService
{
    protected $token;
    protected $baseUrl = 'https://api.line.me/v2/bot/message';

    public function __construct()
    {
        $this->token = config('services.line.access_token');
    }

    /**
     * 【全員送信】友だち登録している全員に送る
     */
    public function broadcast(string $message)
    {
        $response = Http::withToken($this->token)
            ->post($this->baseUrl . '/broadcast', [
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ]);

        return $this->handleResponse($response, '全員送信');
    }

    /**
     * 【個別送信】特定のユーザーIDに送る
     */
    public function push(string $userId, string $message)
    {
        $response = Http::withToken($this->token)
            ->post($this->baseUrl . '/push', [
                'to' => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ]);

        return $this->handleResponse($response, '個別送信');
    }

    // 共通のレスポンス処理
    private function handleResponse($response, $type)
    {
        if ($response->successful()) {
            Log::info("LINE {$type}成功");
            return true;
        }

        Log::error("LINE {$type}失敗: " . $response->body());
        return false;
    }

    /**
     * ボタン付きの特別なメッセージを送る
     */
    public function sendConfirmMessage(string $userId, string $text, string $url)
    {
        return Http::withToken($this->token)
            ->post($this->baseUrl . '/push', [
                'to' => $userId,
                'messages' => [
                    [
                        'type' => 'template',
                        'altText' => '重要なお知らせがあります',
                        'template' => [
                            'type' => 'buttons',
                            'text' => $text,
                            'actions' => [
                                [
                                    'type' => 'uri',
                                    'label' => '詳細を見る',
                                    'uri' => $url
                                ]
                            ]
                        ]
                    ]
                ],
            ]);
    }
}