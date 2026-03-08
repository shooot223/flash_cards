<?php

namespace App\Http\Controllers;

use App\Models\QuestionTitle;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizPlayController extends Controller
{
    public function start($id)
    {
        $quiz = QuestionTitle::with(['questions', 'categories'])->findOrFail($id);

        $latestScore = null;

        if (Auth::check()) {
            $latestScore = Score::where('user_id', Auth::id())
                ->where('title_id', $quiz->id)
                ->latest('created_at')
                ->first();
        }

        session()->forget("quiz_progress.$id");

        return view('quiz_start', compact('quiz', 'latestScore'));
    }

    public function play(Request $request, $id)
    {
        $quiz = QuestionTitle::with(['questions.choices'])->findOrFail($id);

        $questions = $quiz->questions->values();
        $step = (int) $request->query('step', 0);

        if (!isset($questions[$step])) {
            return redirect()->route('quiz.result', $quiz->id);
        }

        $question = $questions[$step];
        $total = $questions->count();

        return view('quiz_play', compact('quiz', 'question', 'step', 'total'));
    }

    public function answer(Request $request, $id)
    {
        $quiz = QuestionTitle::with(['questions.choices'])->findOrFail($id);
        $questions = $quiz->questions->values();

        $step = (int) $request->input('step');

        if (!isset($questions[$step])) {
            return redirect()->route('quiz.result', $quiz->id);
        }

        $request->validate([
            'choice_id' => ['required', 'integer'],
            'confidence' => ['required', 'in:high,medium,low'],
        ]);

        $question = $questions[$step];
        $selectedChoice = $question->choices->firstWhere('id', (int) $request->choice_id);
        $correctChoice = $question->choices->firstWhere('is_correct', true);

        $isCorrect = $correctChoice && $selectedChoice && (int)$selectedChoice->id === (int)$correctChoice->id;

        $progress = session()->get("quiz_progress.$id", []);
        $progress[$question->id] = [
            'choice_id' => (int) $request->choice_id,
            'confidence' => $request->confidence,
        ];
        session()->put("quiz_progress.$id", $progress);

        $isLast = !isset($questions[$step + 1]);
        $confidence = $request->confidence;

        return view('quiz_answer', compact(
            'quiz',
            'question',
            'step',
            'selectedChoice',
            'correctChoice',
            'isCorrect',
            'isLast',
            'confidence'
        ));
    }

    public function next(Request $request, $id)
    {
        $quiz = QuestionTitle::with(['questions'])->findOrFail($id);
        $questions = $quiz->questions->values();

        $step = (int) $request->input('step');
        $nextStep = $step + 1;

        if (!isset($questions[$nextStep])) {
            return redirect()->route('quiz.result', $quiz->id);
        }

        return redirect()->route('quiz.play', [
            'id' => $quiz->id,
            'step' => $nextStep,
        ]);
    }

    public function result($id)
    {
        $quiz = QuestionTitle::with(['questions.choices'])->findOrFail($id);

        $progress = session()->get("quiz_progress.$id", []);
        $questions = $quiz->questions->values();

        $score = 0;
        $resultDetails = [];

        $confidenceLabels = [
            'high' => '高い',
            'medium' => '普通',
            'low' => '低い',
        ];

        foreach ($questions as $question) {
            $selected = $progress[$question->id] ?? null;
            $selectedChoiceId = $selected['choice_id'] ?? null;
            $confidence = $selected['confidence'] ?? null;

            $selectedChoice = $question->choices->firstWhere('id', (int) $selectedChoiceId);
            $correctChoice = $question->choices->firstWhere('is_correct', true);

            $isCorrect = $correctChoice && $selectedChoice && (int)$selectedChoice->id === (int)$correctChoice->id;

            if ($isCorrect) {
                $score++;
            }

            $resultDetails[] = [
                'question_text' => $question->question_text,
                'selected_answer' => $selectedChoice?->choice_text ?? '未回答',
                'correct_answer' => $correctChoice?->choice_text ?? '未設定',
                'confidence' => $confidence
                    ? ($confidenceLabels[$confidence] ?? $confidence)
                    : '未回答',
                'is_correct' => $isCorrect,
            ];
        }

        $total = $questions->count();

        if (Auth::check()) {
            Score::create([
                'user_id' => Auth::id(),
                'title_id' => $quiz->id,
                'score_value' => $score,
                'answered_count' => count($progress),
                'correct_count' => $score,
            ]);
        }

        session()->forget("quiz_progress.$id");

        return view('quiz_result', compact('quiz', 'score', 'total', 'resultDetails'));
    }
}
