<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuizAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'description' => ['required','string'],

            'tags' => ['array'],
            'tags.*' => ['string','max:50'],

            'questions' => ['required','array','min:1'],

            'questions.*.question_text' => ['required','string'],

            'questions.*.choices' => ['required','array','size:4'],
            'questions.*.choices.*' => ['required','string'],

            'questions.*.correct_answer' => ['required','integer','between:0,3'],
        ];
    }

    public function messages(): array
    {
        return [

            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',

            'description.string' => '説明文は文字列で入力してください。',

            'tags.array' => 'タグの形式が不正です。',
            'tags.*.string' => 'タグは文字列で入力してください。',
            'tags.*.max' => 'タグは50文字以内で入力してください。',

            'questions.required' => '問題は必須です。',
            'questions.array' => '問題の形式が不正です。',
            'questions.min' => '問題は1問以上必要です。',

            'questions.*.question_text.required' => '問題文は必須です。',
            'questions.*.question_text.string' => '問題文は文字列で入力してください。',

            'questions.*.choices.required' => '選択肢は必須です。',
            'questions.*.choices.array' => '選択肢の形式が不正です。',
            'questions.*.choices.size' => '選択肢は4つ必要です。',

            'questions.*.choices.*.required' => '選択肢を入力してください。',
            'questions.*.choices.*.string' => '選択肢は文字列で入力してください。',

            'questions.*.correct_answer.required' => '正解は必須です。',
            'questions.*.correct_answer.integer' => '正解の形式が不正です。',
            'questions.*.correct_answer.between' => '正解の値は0~3で入力してください。',
        ];
    }
}
