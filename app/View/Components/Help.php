<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;

class Help extends Component
{
    public string $helpKey;
    public ?array $help;

    public function __construct(string $helpKey)
    {
        $this->helpKey = $helpKey;

        // 可変階層対応：そのまま config パスとして取得
        $help = config('help.' . $helpKey);

        if (!is_array($help)) {
            $this->handleInvalidKey('定義が見つかりません');
            return;
        }

        // 必須キーの最低限チェック
        if (!isset($help['title'], $help['body'])) {
            $this->handleInvalidKey('title または body が未定義です');
            return;
        }

        $this->help = $help;
    }

    protected function handleInvalidKey(string $reason): void
    {
        if (App::environment('local')) {
            throw new InvalidArgumentException(
                "Help key '{$this->helpKey}' は無効です（{$reason}）"
            );
        }

        // 本番では表示しない
        $this->help = null;
    }

    public function render()
    {
        return view('components.help');
    }
}
