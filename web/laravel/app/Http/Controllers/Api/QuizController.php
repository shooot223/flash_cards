<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Answer;
use App\Models\Choice;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\QuestionTitle;
use App\Models\QuestionTitleCategory;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    //クイズ保存処理（API）
    public function store(QuizRequest $request): JsonResponse
    {
        //リクエスト内容のバリデーション
        $validated = $request->validated();

        //保存処理
        $title = DB::transaction(function () use ($validated) {

            //クイズのタイトルを保存
            $title = QuestionTitle::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'user_id' => $request->user()->id,
                'is_public' => $validated['is_public'],
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

        return response()->json([
            'message' => 'クイズを作成しました。',
            'data' => new QuizResource($title),
        ], 201);
    }

    //タグの保存処理
    private function syncTags(QuestionTitle $title, array $tags): void
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
            QuestionTitleCategory::create([
                'title_id' => $title->id,
                'category_id' => $category->id,
            ]);
        }
    }

    //クイズの問題の置き換え処理
    private function replaceQuestions(QuestionTitle $title, array $questions): void
    {
        //既存の問題・選択肢があるか確認
        $questionIds = Question::where('title_id', $title->id)->pluck('id');
        //義損の選択肢を削除
        Choice::whereIn('question_id', $questionIds)->delete();
        //既存の問題を削除
        Question::where('title_id', $title->id)->delete();


        //空の問題があれば削除し、配列をつくり直す
        $filteredQuestions = $this->normalizeQuestions($questions);
        foreach ($filteredQuestions as $q) {
            $question = Question::create([
                'title_id' => $title->id,
                'question_text' => $q['question_text'],
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
