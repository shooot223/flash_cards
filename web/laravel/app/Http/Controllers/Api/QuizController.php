<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuizAPIRequest;
use App\Http\Resources\QuizResource;
use App\Models\Answer;
use App\Models\Choice;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Quiz;
use App\Models\QuizCategory;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    //クイズ保存処理（API）
    public function store(QuizAPIRequest $request): JsonResponse
    {
        //リクエスト内容のバリデーション
        $validated = $request->validated();
        $user = $request->user();

        //保存処理
        $title = DB::transaction(function () use ($validated, $user) {

            //クイズのタイトルを保存
            $title = Quiz::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'user_id' => $user->id,
                'is_public' => true,
            ]);

            //タグの保存処理
            $this->syncTags($title, $validated['tags'] ?? []);
            //問題、選択肢の保存処理
            $this->replaceQuestions($title, $validated['questions']);

            return $title->load([
                'categories:id,category_name',
                'questions.choices'
            ]);
        });

        return (new QuizResource($title))
            ->additional([
                'message' => 'クイズを作成しました。'
            ])
            ->response()
            ->setStatusCode(201);
    }

    //タグの保存処理
    private function syncTags(Quiz $title, array $tags): void
    {
        //タグを取ってきて、空タグは排除
        $filteredTags = collect($tags)
            ->filter(fn ($tag) => !empty($tag))
            ->unique()
            ->values();

        foreach ($filteredTags as $tagName) {
            //category_nameカラムに存在するか確認して、なければ作成
            $category = QuestionCategory::firstOrCreate([
                'category_name' => $tagName,
            ]);

            //タグとクイズの関連付けを作成
            QuizCategory::create([
                'quiz_id' => $title->id,
                'category_id' => $category->id,
            ]);
        }
    }

    //クイズの問題の置き換え処理
    private function replaceQuestions(Quiz $title, array $questions): void
    {
        //既存の問題・選択肢があるか確認
        $questionIds = Question::where('quiz_id', $title->id)->pluck('id');
        //義損の選択肢を削除
        Choice::whereIn('question_id', $questionIds)->delete();
        //既存の問題を削除
        Question::where('quiz_id', $title->id)->delete();


        //空の問題があれば削除し、配列をつくり直す
        $filteredQuestions = $this->normalizeQuestions($questions);
        foreach ($filteredQuestions as $q) {
            $question = Question::create([
                'quiz_id' => $title->id,
                'question_text' => $q['question_text'],
                'explanation' => $q['explanation'],
            ]);

            foreach (($q['choices'] ?? []) as $index => $choiceText) {
                if (empty($choiceText)) {
                    continue;
                }

                Choice::create([
                    'question_id' => $question->id,
                    'choice_text' => $choiceText,
                    'is_correct' => (int) $index === (int) $q['correct_answer'] ? 1 : 0,
                ]);
            }
        }
    }

    // 未入力の問題を除外し、問題文・選択肢の前後の空白を削除して配列を整形する
    private function normalizeQuestions(array $questions): array
    {
        return collect($questions)
            ->map(function ($q) {
                return [
                    // 問題文の前後の空白を削除
                    'question_text' => trim((string) ($q['question_text'] ?? '')),

                    // 解説の前後の空白を削除
                    'explanation' => trim((string) ($q['explanation'] ?? '')),

                    // 選択肢の前後の空白を削除
                    'choices' => collect($q['choices'] ?? [])
                        ->map(fn ($choice) => trim((string) $choice))
                        ->all(),

                    'correct_answer' => $q['correct_answer'] ?? null,
                ];
            })
            // 問題文が空の問題は除外
            ->filter(fn ($q) => filled($q['question_text']))
            ->values()
            ->all();
    }
}
