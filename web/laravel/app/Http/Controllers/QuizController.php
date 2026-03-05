<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function create()
    {
        return view('quiz_create');
    }
    public function confirm(Request $request)
    {
//        dd($request->all());
        $validated = $request->validate(
            [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],

                'tags' => ['array'],
                'tags.*' => ['nullable', 'string', 'max:50'],

                // 配列自体は必須
                'questions' => ['required', 'array', 'min:1'],

                // 1問目だけ必須
                'questions.0.question' => ['required', 'string'],
                'questions.0.answer' => ['required', 'string'],
                'questions.0.choices' => ['required', 'array'],
                'questions.0.choices.*' => ['required', 'string'],

                // 2問目以降は任意
                'questions.*.question' => ['nullable', 'string'],
                'questions.*.answer' => ['nullable','required_with:questions.*.question', 'string'],
                'questions.*.choices' => ['nullable', 'required_with:questions.*.question', 'array'],
                'questions.*.choices.*' => ['nullable', 'required_with:questions.*.question', 'string'],
            ],
            [
                'title.required' => 'タイトルは必須です。',
                'title.string' => 'タイトルは文字列で入力してください。',
                'title.max' => 'タイトルは255文字以内で入力してください。',
                'description.required' => '説明は必須です。',
                'description.string' => '説明は文字列で入力してください。',
                'questions.0.question.required' => '1問目の問題は必須です。',
                'questions.0.answer.required' => '1問目の答えは必須です。',
                'questions.0.choices.*.required' => '1問目の選択肢は必須です。',
                'questions.*.answer.required_with' => '問題が入力されていた場合答えは必須です。',
                'questions.*.choices.*.required_with' => '問題が入力されていた場合選択肢は必須です。',
                'questions.*.choices.*.string' => '選択肢は文字列で入力してください。',
            ]
        );

        // 空タグ削除
        $validated['tags'] = array_values(
            array_filter($validated['tags'] ?? [])
        );

        // 空の問題を除外（削除されたケース対策）
        $validated['questions'] = array_values(
            array_filter($validated['questions'], function ($q) {
                return !empty($q['question']) && !empty($q['answer']);
            })
        );

        return view('quiz_confirm', [
            'quiz' => $validated
        ]);
    }
}
