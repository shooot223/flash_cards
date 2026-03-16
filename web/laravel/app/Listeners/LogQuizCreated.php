<?php

namespace App\Listeners;

use App\Events\QuizCreated;
use Illuminate\Support\Facades\Log;

class LogQuizCreated
{
    /**
     * イベントリスナーを生成する
     */
    public function __construct()
    {
        //
    }

    /**
     * クイズ作成イベントを処理する
     *
     * @param QuizCreated $event 発火されたクイズ作成イベント
     */
    public function handle(QuizCreated $event): void
    {
        $quiz = $event->quiz;

        // タグ（カテゴリ）を取得
        $tags = $quiz->categories->pluck('category_name')->join(', ');

        // 問題数を取得
        $questionCount = $quiz->questions->count();

        Log::info('クイズが作成されました', [
            'quiz_id'        => $quiz->id,
            'title'          => $quiz->title,
            'description'    => $quiz->description,
            'tags'           => $tags ?: 'なし',
            'question_count' => $questionCount,
            'image_path'     => $quiz->image_path ?? 'なし',
            'user_id'        => $quiz->user_id,
            'created_at'     => $quiz->created_at?->toDateTimeString(),
        ]);
    }
}
