<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function create(Request $request)
    {
        // confirmから「修正する」でPOSTされてきた場合は、その値を old として使えるようにする
        // GET直叩きの場合は何もしない（空フォーム）
        if ($request->isMethod('post')) {
            return redirect()->route('quiz.create')->withInput($request->all());
        }

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

            // 1問目必須
            'questions.0.question' => ['required', 'string'],
            'questions.0.answer' => ['required', 'string'],
            'questions.0.choices' => ['required', 'array'],
            'questions.0.choices.*' => ['required', 'string'],

            // 2問目以降は任意（入力があれば必須チェック）
            'questions.*.question' => ['nullable', 'string'],
            'questions.*.answer' => ['nullable', 'required_with:questions.*.question', 'string'],
            'questions.*.choices' => ['nullable', 'required_with:questions.*.question', 'array'],
            'questions.*.choices.*' => ['nullable', 'required_with:questions.*.question', 'string'],
        ]);

        return view('quiz_confirm', ['data' => $validated]);
    }

    public function store(Request $request)
    {
        // confirm -> store も hidden をそのまま受けるので validate し直し推奨
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],

            'tags' => ['array'],
            'tags.*' => ['nullable', 'string', 'max:50'],

            'questions' => ['required', 'array', 'min:1'],
            'questions.0.question' => ['required', 'string'],
            'questions.0.answer' => ['required', 'string'],
            'questions.0.choices' => ['required', 'array'],
            'questions.0.choices.*' => ['required', 'string'],

            'questions.*.question' => ['nullable', 'string'],
            'questions.*.answer' => ['nullable', 'required_with:questions.*.question', 'string'],
            'questions.*.choices' => ['nullable', 'required_with:questions.*.question', 'array'],
            'questions.*.choices.*' => ['nullable', 'required_with:questions.*.question', 'string'],
        ]);

        // TODO: 保存処理（例）
        // Quiz::create(...)

        return redirect()->route('quiz.create')->with('status', '作成しました');
    }
}
