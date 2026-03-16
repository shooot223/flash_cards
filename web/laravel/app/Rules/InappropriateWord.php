<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InappropriateWord implements ValidationRule
{
    protected array $badWords = [
        'バカ',
        '馬鹿',
        'アホ',
        'クズ',
        'ゴミ',
        'カス',
        '無能',
        '役立たず',
        '死ね',
        '消えろ',
        '殺す',
        'ぶっ殺す',
        '気持ち悪い',
        'キモい',
        '頭おかしい',
        '殺人',
        '爆破',
        'テロ',
        '自爆',
        '銃撃',
        'リンチ',
        '脅迫',
        '暴行',
        '拷問',
        '誘拐',
        'セックス',
        '性交',
        '性行為',
        'エロ',
        'アダルト',
        'ポルノ',
        '裸',
        'ヌード',
        '外人',
        '黒人差別語',
        '障害者差別語',
        '部落差別語',
        '奴隷',
        '自殺',
        '死にたい',
        'リストカット',
        '首吊り',
        '飛び降り',
        '覚醒剤',
        '麻薬',
        '大麻',
        'コカイン',
        'ドラッグ',
        '詐欺',
        '不正アクセス',
        'ハッキング',
        'クレカ不正',
        '闇バイト',
        'マネーロンダリング',
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
