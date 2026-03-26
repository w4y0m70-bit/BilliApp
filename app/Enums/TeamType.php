<?php
//チームタイプ
namespace App\Enums;

enum TeamType: int
{
    case Singles = 1;
    case Doubles = 2;
    case Trios = 3;
    case Quartet = 4;
    case Quintet = 5;

    public function label(): string
    {
        return match($this) {
            self::Singles => 'シングルス',
            self::Doubles => 'ダブルス',
            self::Trios   => 'トリオス',
            self::Quartet => 'カルテット',
            self::Quintet => 'クインテット',
        };
    }

    public function colorClass(): string
    {
        return match($this) {
            // シングルスは控えめなグレー
            self::Singles => 'bg-gray-100 text-gray-400 border border-gray-200 font-bold',
            self::Doubles => 'bg-pink-200 text-pink-500 font-bold',
            self::Trios => 'bg-purple-200 text-purple-500 font-bold',
            // それ以外はアクセントカラー（user）
            default       => 'bg-user text-Stone font-black shadow-sm',
        };
    }

    public static function fromSize(int $size): self
    {
        return self::tryFrom($size) ?? self::Singles; // 定義外はシングルス扱い（または5名1組等の処理）
    }
}