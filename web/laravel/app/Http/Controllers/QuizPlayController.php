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
                ->latest()
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

        foreach ($questions as $question) {
            $selected = $progress[$question->id] ?? null;
            $selectedChoiceId = $selected['choice_id'] ?? null;
            $confidence = $selected['confidence'] ?? null;
            $correctChoice = $question->choices->firstWhere('is_correct', true);

            $isCorrect = $correctChoice && ((int) $selectedChoiceId === (int) $correctChoice->id);

            if ($isCorrect) {
                $score++;
            }

            $resultDetails[] = [
                'question' => $question,
                'selected_choice_id' => $selectedChoiceId,
                'correct_choice_id' => $correctChoice?->id,
                'confidence' => $confidence,
                'is_correct' => $isCorrect,
            ];
        }

        if (Auth::check()) {
            Score::create([
                'user_id' => Auth::id(),
                'title_id' => $quiz->id,
                'score' => $score,
            ]);
        }

        session()->forget("quiz_progress.$id");

        $total = $questions->count();

        return view('quiz_result', compact('quiz', 'score', 'total', 'resultDetails'));
    }
}
