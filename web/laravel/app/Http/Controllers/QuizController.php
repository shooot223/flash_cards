<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\QuestionTitle;
use App\Models\QuestionCategory;
use App\Models\QuestionTitleCategory;
use App\Models\Question;
use App\Models\Choice;

class QuizController extends Controller
{
    public function create(Request $request)
    {
        return view('quiz_create', [
            'oldFormData' => [
                'title' => $request->input('title', ''),
                'description' => $request->input('description', ''),
                'tags' => $request->input('tags', []),
                'questions' => $request->input('questions', []),
            ],
            'tempQuizImage' => $request->input('temp_quiz_image'),
            'currentImagePath' => $request->input('current_image_path'),
            'isEditFromConfirm' => (bool) $request->input('is_edit', false),
            'quizIdFromConfirm' => $request->input('quiz_id'),
        ]);
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $isEdit = (bool) $request->input('is_edit', false);
        $quizId = $request->input('quiz_id');

        $tempQuizImage = $request->input('temp_quiz_image');
        $currentImagePath = $request->input('current_image_path');

        if ($request->hasFile('quiz_image')) {
            if (!empty($tempQuizImage) && Storage::disk('public')->exists($tempQuizImage)) {
                Storage::disk('public')->delete($tempQuizImage);
            }

            $tempQuizImage = $request->file('quiz_image')->store('tmp/quizzes', 'public');
        }

        return view('quiz_confirm', [
            'formData' => $validated,
            'isEdit' => $isEdit,
            'quizId' => $quizId,
            'tempQuizImage' => $tempQuizImage,
            'currentImagePath' => $currentImagePath,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->storeUpdateRules(),
            $this->messages()
        );

        DB::transaction(function () use ($validated) {
            $imagePath = null;

            if (!empty($validated['temp_quiz_image']) && Storage::disk('public')->exists($validated['temp_quiz_image'])) {
                $filename = basename($validated['temp_quiz_image']);
                $newPath = 'quizzes/' . $filename;

                Storage::disk('public')->move($validated['temp_quiz_image'], $newPath);
                $imagePath = $newPath;
            }

            $title = QuestionTitle::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'user_id' => auth()->id(),
                'image_path' => $imagePath,
            ]);

            $this->syncTags($title, $validated['tags'] ?? []);
            $this->replaceQuestions($title, $validated['questions']);
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
            $this->storeUpdateRules(),
            $this->messages()
        );

        DB::transaction(function () use ($validated, $id) {
            $title = QuestionTitle::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $imagePath = $validated['current_image_path'] ?? $title->image_path;

            if (!empty($validated['temp_quiz_image']) && Storage::disk('public')->exists($validated['temp_quiz_image'])) {
                if (!empty($title->image_path) && Storage::disk('public')->exists($title->image_path)) {
                    Storage::disk('public')->delete($title->image_path);
                }

                $filename = basename($validated['temp_quiz_image']);
                $newPath = 'quizzes/' . $filename;

                Storage::disk('public')->move($validated['temp_quiz_image'], $newPath);
                $imagePath = $newPath;
            }

            $title->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'image_path' => $imagePath,
            ]);

            QuestionTitleCategory::where('title_id', $title->id)->delete();
            $this->syncTags($title, $validated['tags'] ?? []);
            $this->replaceQuestions($title, $validated['questions']);
        });

        return redirect()->route('quiz.complete');
    }

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

    private function syncTags(QuestionTitle $title, array $tags): void
    {
        $filteredTags = collect($tags)
            ->filter(fn ($tag) => !empty($tag))
            ->unique()
            ->values();

        foreach ($filteredTags as $tagName) {
            $category = QuestionCategory::firstOrCreate([
                'category_name' => $tagName,
            ]);

            QuestionTitleCategory::create([
                'title_id' => $title->id,
                'category_id' => $category->id,
            ]);
        }
    }

    private function replaceQuestions(QuestionTitle $title, array $questions): void
    {
        $questionIds = Question::where('title_id', $title->id)->pluck('id');
        Choice::whereIn('question_id', $questionIds)->delete();
        Question::where('title_id', $title->id)->delete();

        $filteredQuestions = collect($questions)
            ->filter(fn ($q) => !empty($q['question']))
            ->values();

        foreach ($filteredQuestions as $q) {
            $question = Question::create([
                'title_id' => $title->id,
                'question_text' => $q['question'],
            ]);

            foreach (($q['choices'] ?? []) as $index => $choiceText) {
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
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'quiz_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'temp_quiz_image' => ['nullable', 'string'],
            'current_image_path' => ['nullable', 'string'],

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

    private function storeUpdateRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'temp_quiz_image' => ['nullable', 'string'],
            'current_image_path' => ['nullable', 'string'],

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
            'quiz_image.image' => '画像ファイルを選択してください。',
            'quiz_image.mimes' => '画像は jpg / jpeg / png / webp を選択してください。',
            'quiz_image.max' => '画像サイズは 2MB 以下にしてください。',

            'questions.0.question.required' => '少なくとも1問は必要です。',
            'questions.0.choices.required' => '少なくとも1問は必要です。',
            'questions.0.choices.size' => '選択肢は4つ必要です。',
            'questions.0.choices.*.required' => '選択肢は必須です。',
            'questions.0.correct.required' => '正解の選択肢を選んでください。',
            'questions.0.correct.between' => '正解の選択肢が不正です。',
        ];
    }
}
