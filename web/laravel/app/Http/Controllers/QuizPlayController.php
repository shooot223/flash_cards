<?php

namespace App\Http\Controllers;

use App\Models\QuestionTitle;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\QuizPlayRequest;

class QuizPlayController extends Controller
{
    /**
     * クイズ開始画面を表示する
     *
     * - クイズ本体、問題、選択肢、カテゴリを取得する
     * - ログイン中なら前回のスコアも取得する
     * - 進捗をリセットする
     * - 問題順と選択肢順をランダムに作成してセッションへ保存する
     */
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

        // 前回の解答進捗を削除する
        session()->forget("quiz_progress.$id");

        // 毎回ランダムな出題順を作成する
        $questionOrder = $quiz->questions
            ->pluck('id')
            ->shuffle()
            ->values()
            ->all();

        // 各問題ごとに選択肢の表示順もランダム化する
        $choiceOrders = [];
        foreach ($quiz->questions as $question) {
            $choiceOrders[$question->id] = $question->choices
                ->pluck('id')
                ->shuffle()
                ->values()
                ->all();
        }

        // 問題順・選択肢順をセッションに保存する
        session()->put("quiz_order.$id", $questionOrder);
        session()->put("quiz_choice_order.$id", $choiceOrders);

        return view('quiz_start', compact('quiz', 'latestScore'));
    }

    /**
     * 指定ステップの問題画面を表示する
     *
     * - セッションに保存した順番で問題を取得する
     * - step パラメータから現在の問題番号を決定する
     * - 範囲外なら結果画面へ遷移する
     */
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

    /**
     * 回答を受け取り、正誤判定結果画面を表示する
     *
     * - 現在の問題を取得する
     * - 選択された choice_id がその問題に属するか確認する
     * - 正解選択肢と比較して正誤を判定する
     * - 解答内容と自信度をセッションへ保存する
     * - 解答結果画面を表示する
     */
    public function answer(QuizPlayRequest $request, $id)
    {
        $quiz = QuestionTitle::with(['questions.choices'])->findOrFail($id);
        $questions = $this->getOrderedQuestions($quiz, $id);

        $step = (int) $request->input('step');

        if (!isset($questions[$step])) {
            return redirect()->route('quiz.result', $quiz->id);
        }

        $question = $questions[$step];

        // 選択された回答が、この問題の選択肢に含まれているか確認する
        $selectedChoice = $question->choices->firstWhere(
            'id',
            (int) $request->choice_id
        );

        if (!$selectedChoice) {
            return back()
                ->withErrors(['choice_id' => '不正な選択肢です。'])
                ->withInput();
        }

        // 正解の選択肢を取得する
        $correctChoice = $question->choices->firstWhere('is_correct', true);

        // 選択した回答と正解を比較して正誤判定する
        $isCorrect = $correctChoice
            && (int) $selectedChoice->id === (int) $correctChoice->id;

        // 回答内容と自信度をセッションに保存する
        $progress = session()->get("quiz_progress.$id", []);
        $progress[$question->id] = [
            'choice_id' => (int) $request->choice_id,
            'confidence' => $request->confidence,
        ];
        session()->put("quiz_progress.$id", $progress);

        // 次の問題が存在しない場合は最終問題と判定する
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

    /**
     * 次の問題へ遷移する
     *
     * - 現在の step を受け取り、次の step を算出する
     * - 次の問題がなければ結果画面へ遷移する
     * - まだ問題があれば次の問題画面へリダイレクトする
     */
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

    /**
     * 結果画面を表示する
     *
     * - セッションから解答履歴を取得する
     * - 各問題ごとの正誤、自信度、選択回答を集計する
     * - ログイン中ならスコアを保存する
     * - 最後に進捗・出題順・選択肢順のセッションを削除する
     */
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

        // ログイン中のユーザーのみスコアを保存する
        if (Auth::check()) {
            Score::create([
                'user_id' => Auth::id(),
                'title_id' => $quiz->id,
                'score_value' => $score,
                'answered_count' => count($progress),
                'correct_count' => $score,
            ]);
        }

        // クイズ終了後は関連セッションを削除する
        session()->forget("quiz_progress.$id");
        session()->forget("quiz_order.$id");
        session()->forget("quiz_choice_order.$id");

        return view('quiz_result', compact('quiz', 'score', 'total', 'resultDetails'));
    }

    /**
     * セッションに保存された順番で問題・選択肢を並び替えて返す
     *
     * - quiz_order で問題順を制御する
     * - quiz_choice_order で各問題の選択肢順を制御する
     * - セッション情報が壊れている場合は元の順序を保険として使用する
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

                // セッションに選択肢順がない場合は元の順序を使う
                if ($orderedChoices->isEmpty()) {
                    $orderedChoices = $question->choices->values();
                }

                $question->setRelation('choices', $orderedChoices);

                return $question;
            })
            ->filter()
            ->values();

        // セッションに問題順がない場合は元の順序を使う
        if ($orderedQuestions->isEmpty()) {
            $orderedQuestions = $quiz->questions->values();
        }

        return $orderedQuestions;
    }
}
