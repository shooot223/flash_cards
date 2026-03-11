<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'is_public' => ['required', 'boolean'],

            'temp_quiz_image' => ['nullable', 'string'],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],

            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.choices' => ['required', 'array', 'min:2'],
            'questions.*.choices.*' => ['required', 'string'],
            'questions.*.correct_answer' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',

            'description.string' => '説明文は文字列で入力してください。',

            'is_public.required' => '公開設定は必須です。',
            'is_public.boolean' => '公開設定の値が不正です。',

            'temp_quiz_image.string' => '画像パスの形式が不正です。',

            'tags.array' => 'タグの形式が不正です。',
            'tags.*.string' => 'タグは文字列で入力してください。',
            'tags.*.max' => 'タグは255文字以内で入力してください。',

            'questions.required' => '問題は必須です。',
            'questions.array' => '問題の形式が不正です。',
            'questions.min' => '問題は1問以上必要です。',

            'questions.*.question_text.required' => '問題文は必須です。',
            'questions.*.question_text.string' => '問題文は文字列で入力してください。',

            'questions.*.choices.required' => '選択肢は必須です。',
            'questions.*.choices.array' => '選択肢の形式が不正です。',
            'questions.*.choices.min' => '選択肢は2つ以上必要です。',

            'questions.*.choices.*.required' => '選択肢を入力してください。',
            'questions.*.choices.*.string' => '選択肢は文字列で入力してください。',

            'questions.*.correct_answer.required' => '正解は必須です。',
            'questions.*.correct_answer.integer' => '正解の形式が不正です。',
            'questions.*.correct_answer.min' => '正解の値が不正です。',
        ];
    }
}
