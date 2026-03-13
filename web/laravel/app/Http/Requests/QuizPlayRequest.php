<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizPlayRequest extends FormRequest
{
    /**
     * このリクエストを許可するか
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション前の整形
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'step' => is_numeric($this->step) ? (int) $this->step : $this->step,
            'choice_id' => is_numeric($this->choice_id) ? (int) $this->choice_id : $this->choice_id,
        ]);
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'step' => ['required', 'integer', 'min:0'],
            'choice_id' => ['required', 'integer'],
            'confidence' => ['required', 'in:high,medium,low'],
        ];
    }

    /**
     * 項目名
     */
    public function attributes(): array
    {
        return [
            'step' => '問題番号',
            'choice_id' => '回答',
            'confidence' => '自信度',
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'step.required' => '問題番号が指定されていません。',
            'step.integer' => '問題番号が不正です。',
            'step.min' => '問題番号が不正です。',

            'choice_id.required' => '回答を選択してください。',
            'choice_id.integer' => '回答の値が不正です。',

            'confidence.required' => '自信度を選択してください。',
            'confidence.in' => '自信度の値が不正です。',
        ];
    }
}
