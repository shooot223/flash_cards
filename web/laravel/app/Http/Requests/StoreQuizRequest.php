<?php

namespace App\Http\Requests;

use App\Rules\InappropriateWord;
use Illuminate\Foundation\Http\FormRequest;

/**
 * クイズ新規作成（確認画面 → 保存）時のバリデーション
 */
class StoreQuizRequest extends FormRequest
{
    /**
     * このリクエストを許可するか
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'title'               => ['required', 'string', 'max:255', new InappropriateWord()],
            'description'         => ['required', 'string', new InappropriateWord()],
            'temp_quiz_image'     => ['nullable', 'string'],
            'current_image_path'  => ['nullable', 'string'],

            'tags'   => ['array'],
            'tags.*' => ['nullable', 'string', 'max:50', new InappropriateWord()],

            // 1問目は必須
            'questions'               => ['required', 'array', 'min:1'],
            'questions.0.question'    => ['required', 'string', new InappropriateWord()],
            'questions.0.choices'     => ['required', 'array', 'size:4'],
            'questions.0.choices.*'   => ['required', 'string', new InappropriateWord()],
            'questions.0.correct'     => ['required', 'integer', 'between:0,3'],

            // 2問目以降はいずれかが入力されていれば全項目必須
            'questions.*.question'    => ['nullable', 'required_with:questions.*.choices,correct', 'string', new InappropriateWord()],
            'questions.*.choices'     => ['nullable', 'required_with:questions.*.question,correct', 'array'],
            'questions.*.choices.*'   => ['nullable', 'required_with:questions.*.question,correct', 'string', new InappropriateWord()],
            'questions.*.correct'     => ['nullable', 'required_with:questions.*.question,choices', 'integer', 'between:0,3'],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'title.required'       => 'タイトルは必須です。',
            'description.required' => '説明は必須です。',

            'questions.0.question.required'  => '少なくとも1問は必要です。',
            'questions.0.choices.required'   => '少なくとも1問は必要です。',
            'questions.0.choices.size'       => '選択肢は4つ必要です。',
            'questions.0.choices.*.required' => '選択肢は必須です。',
            'questions.0.correct.required'   => '正解の選択肢を選んでください。',
            'questions.0.correct.between'    => '正解の選択肢が不正です。',

            'questions.*.question.required_with'   => '選択肢が入力もしくは正解が選択されている場合、問題文は必須です。',
            'questions.*.choices.required_with'    => '問題文が入力もしくは正解が選択されている場合、選択肢は必須です。',
            'questions.*.choices.*.required_with'  => 'この選択肢は必須です。',
            'questions.*.correct.required_with'    => '問題文もしくは選択肢が入力されている場合、正解の選択肢は必須です。',
        ];
    }
}
