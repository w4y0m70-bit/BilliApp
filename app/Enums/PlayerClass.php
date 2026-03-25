<?php

namespace App\Enums;

enum PlayerClass: string
{
    case Pro = 'Pro';
    case SA = 'SA';
    case A = 'A';
    case SB = 'SB';
    case B = 'B';
    case C = 'C';
    case Beginner = 'Beginner';

    // クラスの強さを数値で定義
    public function rank(): int
    {
        return match($this) {
            self::Beginner => 1,
            self::C        => 2,
            self::B        => 3,
            self::SB       => 4,
            self::A        => 5,
            self::SA       => 6,
            self::Pro      => 7,
        };
    }
    
    // 表示用のラベルを返すメソッド
    public function label(): string
    {
        return match($this) {
            self::Beginner => 'Beginner', // プルダウン用
            default => $this->value,      // その他はそのままの値を返す
        };
    }

    // リスト表示用の略称を返すメソッド
    public function shortLabel(): string
    {
        return match($this) {
            self::Beginner => 'Bg',  // 略称にする
            self::Pro => 'P',        // 略称にする
            default => $this->value, // その他はそのまま
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pro      => 'bg-rose-800',
            self::SA       => 'bg-amber-700',
            self::A        => 'bg-amber-600',
            self::SB       => 'bg-sky-600',
            self::B        => 'bg-sky-500',
            self::C        => 'bg-emerald-600',
            self::Beginner => 'bg-emerald-500',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}