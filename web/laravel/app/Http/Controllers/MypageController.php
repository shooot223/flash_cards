<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Score;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    public function display()
    {
        $userId = Auth::id();

        // 作成した問題
        $createdQuizzes = Quiz::where('user_id', $userId)
            ->latest()
            ->get();

        $scoreRecords = Score::where('user_id', $userId)
            ->where('answered_count', '>', 0)
            ->latest('created_at')
            ->get()
            ->unique('quiz_id'); // 最新の回答記録のみを残す

        // 回答したクイズの情報リスト（最終回答日時の最新順に保持）
        $answeredQuizzes = collect();

        foreach ($scoreRecords as $score) {
            $quiz = Quiz::find($score->quiz_id);
            if ($quiz) {
                // 非公開問題で、かつ自分が作成した問題ではない場合はスキップ
                if (!$quiz->is_public && $quiz->user_id !== $userId) {
                    continue;
                }

                // ビュー上で「いつ回答したか」を表示するためのプロパティをセット
                $quiz->latest_answered_at = $score->created_at;
                $answeredQuizzes->push($quiz);
            }
        }

        return view('mypage', [
            'createdQuizzes' => $createdQuizzes,
            'answeredQuizzes' => $answeredQuizzes
        ]);
    }
}
