<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\QuestionTitle;
use App\Models\QuestionCategory;
use App\Models\QuestionTitleCategory;
use App\Models\Question;
use App\Models\Choice;

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
        ],
        [
            'questions.0.question.required' => '少なくとも1問は必要です。',
            'questions.0.answer.required' => '少なくとも1問は必要です。',
            'questions.0.choices.required' => '少なくとも1問は必要です。',
            'questions.0.choices.*.required' => '少なくとも1問は必要です。',
            'questions.*.answer.required_with' => '問題文に入力があった場合は回答は必須です。',
            'questions.*.choices.*.required_with' => '問題文に入力があった場合は回答は必須です。',
        ]
        );

        return view('quiz_confirm', ['data' => $validated]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['required','string'],

            'tags' => ['array'],
            'tags.*' => ['nullable','string','max:50'],

            'questions' => ['required','array','min:1'],

            'questions.0.question' => ['required','string'],
            'questions.0.answer' => ['required','string'],
            'questions.0.choices' => ['required','array'],
            'questions.0.choices.*' => ['required','string'],

            'questions.*.question' => ['nullable','string'],
            'questions.*.answer' => ['nullable','required_with:questions.*.question','string'],
            'questions.*.choices' => ['nullable','required_with:questions.*.question','array'],
            'questions.*.choices.*' => ['nullable','required_with:questions.*.question','string'],
        ]);

        DB::transaction(function () use ($validated) {

            // ① クイズタイトル作成
            $title = QuestionTitle::create([
                'title' => $validated['title'],
                'description' => $validated['description'], // ← DBスペルに合わせる
                'user_id' => auth()->id(), // 必須
            ]);

            // ② カテゴリ登録
            $tags = collect($validated['tags'] ?? [])
                ->filter(fn($tag) => !empty($tag))
                ->unique()
                ->values();

            foreach ($tags as $tagName) {

                $category = QuestionCategory::firstOrCreate([
                    'category_name' => $tagName
                ]);

                QuestionTitleCategory::create([
                    'title_id' => $title->id,
                    'category_id' => $category->id,
                ]);
            }

            // ③ 問題登録
            $questions = collect($validated['questions'])
                ->filter(fn($q) => !empty($q['question']))
                ->values();

            foreach ($questions as $q) {

                $question = Question::create([
                    'title_id' => $title->id,
                    'question_text' => $q['question'],
                ]);

                // ④ 選択肢登録
                foreach ($q['choices'] as $choiceText) {

                    if (empty($choiceText)) {
                        continue;
                    }

                    Choice::create([
                        'question_id' => $question->id,
                        'choice_text' => $choiceText,
                        'is_correct' => trim($choiceText) === trim($q['answer']),
                    ]);
                }
            }

        });

        return redirect()->route('quiz.complete');
    }
    Public function complete()
    {
        return view('quiz_complete');
    }
}
