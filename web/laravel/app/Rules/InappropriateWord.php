<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InappropriateWord implements ValidationRule
{
    protected array $badWords = [
        'バカ',
        '馬鹿',
        'ばか',
        'アホ',
        'あほ',
        'クズ',
        'くず',
        'ゴミ',
        'ごみ',
        'カス',
        'かす',
        '無能',
        'むのう',
        '役立たず',
        '死ね',
        'しね',
        '消えろ',
        'きえろ',
        '殺す',
        'ころす',
        'ぶっ殺す',
        '気持ち悪い',
        'キモい',
        '頭おかしい',
        '殺人',
        '自爆',
        'リンチ',
        'セックス',
        '性交',
        '性行為',
        'エロ',
        'ポルノ',
        '裸',
        'ヌード',
        '奴隷',
        '自殺',
        '死にたい',
        'リストカット',
        '首吊り',
    ];

    protected array $exactMatchWords = [
        'AV',
        'SEX',
        'LSD',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $normalized = mb_strtolower(trim($value));

        foreach ($this->exactMatchWords as $word) {
            if ($normalized === mb_strtolower($word)) {
                $fail('不適切な単語が含まれています。');
                return;
            }
        }

        foreach ($this->badWords as $word) {
            if (mb_stripos($value, $word) !== false) {
                $fail('不適切な単語が含まれています。');
                return;
            }
        }
    }
}
