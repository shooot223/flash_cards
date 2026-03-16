<?php

namespace App\Events;

use App\Models\QuestionTitle;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 作成されたクイズのモデル
     */
    public QuestionTitle $quiz;

    /**
     * イベントインスタンスを生成する
     *
     * @param QuestionTitle $quiz 新しく作成されたクイズ
     */
    public function __construct(QuestionTitle $quiz)
    {
        $this->quiz = $quiz;
    }
}
