<?php

namespace App\Events;

use App\Models\Quiz;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * 作成されたクイズのモデル
     */
    public Quiz $quiz;

    /**
     * イベントインスタンスを生成する
     *
     * @param Quiz $quiz 新しく作成されたクイズ
     */
    public function __construct(Quiz $quiz)
    {
        $this->quiz = $quiz;
    }
}
