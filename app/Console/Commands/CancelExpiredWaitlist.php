<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserEntry;
use Carbon\Carbon;
use App\Services\WaitlistService;

class CancelExpiredWaitlist extends Command
{
    protected $signature = 'userentry:cancel-expired';
    protected $description = 'キャンセル待ち期限が切れたエントリーをキャンセル';

    public function handle(WaitlistService $service)
    {
        $this->info('コマンドを開始します...');
        // サービスを呼び出すだけ！
        $service->handleExpiredWaitlist();

        $this->info('期限切れ処理を完了しました');
        return 0;
    }
}
