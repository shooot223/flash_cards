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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],

            'tags' => ['array'],
            'tags.*' => ['nullable', 'string', 'max:50'],

            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string'],
            'questions.*.answer' => ['required', 'string'],
            'questions.*.choices' => ['array'],
            'questions.*.choices.*' => ['nullable', 'string'],
        ]);

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
