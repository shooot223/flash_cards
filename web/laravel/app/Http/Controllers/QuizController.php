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
        if ($request->isMethod('post')) {
            return redirect()->route('quiz.create')->withInput($request->all());
        }

        return view('quiz_create');
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $isEdit = (bool) $request->input('is_edit', false);
        $quizId = $request->input('quiz_id');

        return view('quiz_confirm', [
            'formData' => $validated,
            'isEdit' => $isEdit,
            'quizId' => $quizId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        DB::transaction(function () use ($validated) {
            $title = QuestionTitle::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'user_id' => auth()->id(),
            ]);

            $tags = collect($validated['tags'] ?? [])
                ->filter(fn($tag) => !empty($tag))
                ->unique()
                ->values();

            foreach ($tags as $tagName) {
                $category = QuestionCategory::firstOrCreate([
                    'category_name' => $tagName,
                ]);

                QuestionTitleCategory::create([
                    'title_id' => $title->id,
                    'category_id' => $category->id,
                ]);
            }

            $questions = collect($validated['questions'])
                ->filter(fn($q) => !empty($q['question']))
                ->values();

            foreach ($questions as $q) {
                $question = Question::create([
                    'title_id' => $title->id,
                    'question_text' => $q['question'],
                ]);

                foreach ($q['choices'] as $index => $choiceText) {
                    if (empty($choiceText)) {
                        continue;
                    }

                    Choice::create([
                        'question_id' => $question->id,
                        'choice_text' => $choiceText,
                        'is_correct' => (int) $index === (int) $q['correct'],
                    ]);
                }
            }
        });

        return redirect()->route('quiz.complete');
    }

    public function complete()
    {
        return view('quiz_complete');
    }

    public function edit($id)
    {
        $quiz = QuestionTitle::with(['questions.choices', 'categories'])->findOrFail($id);

        return view('quiz_create', compact('quiz'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        DB::transaction(function () use ($validated, $id) {
            $title = QuestionTitle::findOrFail($id);

            $title->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);

            QuestionTitleCategory::where('title_id', $title->id)->delete();

            $tags = collect($validated['tags'] ?? [])
                ->filter(fn($tag) => !empty($tag))
                ->unique()
                ->values();

            foreach ($tags as $tagName) {
                $category = QuestionCategory::firstOrCreate([
                    'category_name' => $tagName,
                ]);

                QuestionTitleCategory::create([
                    'title_id' => $title->id,
                    'category_id' => $category->id,
                ]);
            }

            $questionIds = Question::where('title_id', $title->id)->pluck('id');
            Choice::whereIn('question_id', $questionIds)->delete();
            Question::where('title_id', $title->id)->delete();

            $questions = collect($validated['questions'])
                ->filter(fn($q) => !empty($q['question']))
                ->values();

            foreach ($questions as $q) {
                $question = Question::create([
                    'title_id' => $title->id,
                    'question_text' => $q['question'],
                ]);

                foreach ($q['choices'] as $index => $choiceText) {
                    if (empty($choiceText)) {
                        continue;
                    }

                    Choice::create([
                        'question_id' => $question->id,
                        'choice_text' => $choiceText,
                        'is_correct' => (int) $index === (int) $q['correct'],
                    ]);
                }
            }
        });

        return redirect()->route('quiz.complete');
    }

    //物理削除
//    public function delete($id)
//    {
//        DB::transaction(function () use ($id) {
//
//            $quiz = QuestionTitle::where('id', $id)
//                ->where('user_id', auth()->id())
//                ->firstOrFail();
//
//            foreach ($quiz->questions as $question) {
//                $question->choices()->delete();
//            }
//            $quiz->questions()->delete();
//            $quiz->categories()->detach();
//            $quiz->delete();
//        });
//
//        return redirect()->route('mypage')->with('success', '問題を削除しました。');
//    }

    public function private($id)
    {
        $quiz = QuestionTitle::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $quiz->update([
            'is_public' => false,
        ]);

        return redirect()->route('mypage')->with('success', '問題を非公開にしました。');
    }

    public function public($id)
    {
        $quiz = QuestionTitle::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $quiz->update([
            'is_public' => true,
        ]);

        return redirect()->route('mypage')->with('success', '問題を再公開しました。');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],

            'tags' => ['array'],
            'tags.*' => ['nullable', 'string', 'max:50'],

            'questions' => ['required', 'array', 'min:1'],

            'questions.0.question' => ['required', 'string'],
            'questions.0.choices' => ['required', 'array', 'size:4'],
            'questions.0.choices.*' => ['required', 'string'],
            'questions.0.correct' => ['required', 'integer', 'between:0,3'],

            'questions.*.question' => ['nullable', 'string'],
            'questions.*.choices' => ['nullable', 'array'],
            'questions.*.choices.*' => ['nullable', 'string'],
            'questions.*.correct' => ['nullable', 'integer', 'between:0,3'],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'description.required' => '説明は必須です。',

            'questions.0.question.required' => '少なくとも1問は必要です。',
            'questions.0.choices.required' => '少なくとも1問は必要です。',
            'questions.0.choices.size' => '選択肢は4つ必要です。',
            'questions.0.choices.*.required' => '選択肢は必須です。',
            'questions.0.correct.required' => '正解の選択肢を選んでください。',
            'questions.0.correct.between' => '正解の選択肢が不正です。',
        ];
    }
}
