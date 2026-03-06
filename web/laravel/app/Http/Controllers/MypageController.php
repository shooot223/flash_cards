<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QuestionTitle;
use App\Models\Score;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 作成したクイズ
        $createdQuizzes = QuestionTitle::where('user_id', $userId)
            ->latest()
            ->get();

        // 過去に回答したクイズ
        $answeredQuizzes = QuestionTitle::whereIn(
            'id',
            Score::where('user_id', $userId)->pluck('title_id')
        )->get();

        return view('mypage', [
            'createdQuizzes' => $createdQuizzes,
            'answeredQuizzes' => $answeredQuizzes
        ]);
    }
}
