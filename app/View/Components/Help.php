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

        $parts = explode('.', $helpKey, 2);

        if (count($parts) !== 2) {
            $this->handleInvalidKey("形式が不正です");
            return;
        }

        [$group, $name] = $parts;

        $help = config("help.$group.$name");

        if (!is_array($help)) {
            $this->handleInvalidKey("定義が見つかりません");
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

        // 本番では静かに無視
        $this->help = null;
    }

    public function render()
    {
        return view('components.help');
    }
}
