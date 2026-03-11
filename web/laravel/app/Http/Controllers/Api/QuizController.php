<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Models\Answer;
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
    public function store(StoreQuizRequest $request): JsonResponse
    {
        //リクエスト内容のバリデーション
        $validated = $request->validated();

        //保存処理
        $title = DB::transaction(function () use ($validated) {

            //クイズのタイトルを保存
            $title = QuestionTitle::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'user_id' => 1,
                'is_public' => $validated['is_public'],
            ]);

            //タグの保存処理
            $this->syncTags($title, $validated['tags'] ?? []);
            //問題、選択肢の保存処理
            $this->replaceQuestions($title, $validated['questions']);

            return $title->load([
                'tags',
                'questions.choices',
            ]);
        });

        return response()->json([
            'message' => 'クイズを作成しました。',
            'data' => $title,
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
        //このクイズに紐づく問題を全部削除（updateで活用）
        $title->questions()->delete();

        //クイズの問題を置き換える（新たに作成する）
        foreach ($questions as $questionData) {
            $question = $title->questions()->create([
                'question_text' => $questionData['question_text'],
            ]);

            foreach ($questionData['choices'] as $index => $choiceText) {
                $question->answers()->create([
                    'answer_text' => $choiceText,
                    'is_correct' => $index === (int) $questionData['correct_answer'],
                ]);
            }
        }
    }
}
