<?php

namespace App\Http\Controllers;

use App\Models\QuestionTitle;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\QuizPlayRequest;

class QuizPlayController extends Controller
{
    public function start($id)
    {
        $quiz = QuestionTitle::with(['questions.choices', 'categories'])->findOrFail($id);

        $latestScore = null;

        if (Auth::check()) {
            $latestScore = Score::where('user_id', Auth::id())
                ->where('title_id', $quiz->id)
                ->latest('created_at')
                ->first();
        }

        // 前回の進捗を削除
        session()->forget("quiz_progress.$id");

        // 出題順・選択肢順を毎回新しく作る
        $questionOrder = $quiz->questions
            ->pluck('id')
            ->shuffle()
            ->values()
            ->all();

        $choiceOrders = [];
        foreach ($quiz->questions as $question) {
            $choiceOrders[$question->id] = $question->choices
                ->pluck('id')
                ->shuffle()
                ->values()
                ->all();
        }

        session()->put("quiz_order.$id", $questionOrder);
        session()->put("quiz_choice_order.$id", $choiceOrders);

        return view('quiz_start', compact('quiz', 'latestScore'));
    }

    public function play(Request $request, $id)
    {
        $quiz = QuestionTitle::with(['questions.choices'])->findOrFail($id);

        $questions = $this->getOrderedQuestions($quiz, $id);
        $step = (int) $request->query('step', 0);

        if (!isset($questions[$step])) {
            return redirect()->route('quiz.result', $quiz->id);
        }

        $question = $questions[$step];
        $total = $questions->count();

        return view('quiz_play', compact('quiz', 'question', 'step', 'total'));
    }

    public function answer(QuizPlayRequest $request, $id)
    {
        $quiz = QuestionTitle::with(['questions.choices'])->findOrFail($id);
        $questions = $this->getOrderedQuestions($quiz, $id);

        $step = (int) $request->input('step');

        if (!isset($questions[$step])) {
            return redirect()->route('quiz.result', $quiz->id);
        }

        $question = $questions[$step];
        $selectedChoice = $question->choices->firstWhere('id', (int) $request->choice_id);
        $correctChoice = $question->choices->firstWhere('is_correct', true);

        if (!$selectedChoice) {
            return back()
                ->withErrors(['choice_id' => '不正な選択肢です。'])
                ->withInput();
        }

        $isCorrect = $correctChoice
            && (int) $selectedChoice->id === (int) $correctChoice->id;

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
        $questions = $this->getOrderedQuestions($quiz, $id);

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
        $questions = $this->getOrderedQuestions($quiz, $id);

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

            $isCorrect = $correctChoice
                && $selectedChoice
                && (int) $selectedChoice->id === (int) $correctChoice->id;

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
        session()->forget("quiz_order.$id");
        session()->forget("quiz_choice_order.$id");

        return view('quiz_result', compact('quiz', 'score', 'total', 'resultDetails'));
    }

    /**
     * セッションに保存した順番で問題・選択肢を並べ替える
     */
    private function getOrderedQuestions(QuestionTitle $quiz, int|string $quizId)
    {
        $questionOrder = session()->get("quiz_order.$quizId", []);
        $choiceOrders = session()->get("quiz_choice_order.$quizId", []);

        $questionsById = $quiz->questions->keyBy('id');

        $orderedQuestions = collect($questionOrder)
            ->map(function ($questionId) use ($questionsById, $choiceOrders) {
                $question = $questionsById->get($questionId);

                if (!$question) {
                    return null;
                }

                $choiceOrder = $choiceOrders[$questionId] ?? [];
                $choicesById = $question->choices->keyBy('id');

                $orderedChoices = collect($choiceOrder)
                    ->map(fn ($choiceId) => $choicesById->get($choiceId))
                    ->filter()
                    ->values();

                // セッションに順序が無い場合の保険
                if ($orderedChoices->isEmpty()) {
                    $orderedChoices = $question->choices->values();
                }

                $question->setRelation('choices', $orderedChoices);

                return $question;
            })
            ->filter()
            ->values();

        // セッションが空だった場合の保険
        if ($orderedQuestions->isEmpty()) {
            $orderedQuestions = $quiz->questions->values();
        }

        return $orderedQuestions;
    }
}
