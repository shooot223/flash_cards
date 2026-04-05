<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuizPlayRequest;
use App\Models\Answer;
use App\Models\Confidence;
use App\Models\Quiz;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizPlayController extends Controller
{
    /**
     * クイズ開始画面を表示する
     *
     * - クイズ本体、問題、選択肢、カテゴリを取得する
     * - ログイン中なら前回スコアを取得する
     * - 今回の挑戦用にセッションを初期化する
     * - 問題順、選択肢順をランダムに作成してセッションに保存する
     *
     * ※ この時点では DB に保存しない
     *   （result 到達時のみ正式記録にするため）
     */
    public function start($id)
    {
        $quiz = Quiz::with(['questions.choices', 'categories'])->findOrFail($id);

        // 非公開クイズはオーナー以外アクセス不可
        if (!$quiz->is_public && $quiz->user_id !== Auth::id()) {
            abort(403, 'このクイズは非公開です。');
        }

        $latestScore = null;
        if (Auth::check()) {
            $latestScore = Score::where('user_id', Auth::id())
                ->where('quiz_id', $quiz->id)
                ->latest('created_at')
                ->first();
        }

        // 前回挑戦時のセッション情報を初期化する
        session()->forget("quiz_progress.$id");
        session()->forget("quiz_order.$id");
        session()->forget("quiz_choice_order.$id");
        session()->forget("quiz_result_snapshot.$id");
        session()->forget("quiz_result_saved.$id");

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
     * - セッションに保存した問題順で現在の問題を取得する
     * - step が範囲外なら結果画面へ遷移する
     */
    public function play(Request $request, $id)
    {
        $quiz = Quiz::with(['questions.choices'])->findOrFail($id);

        // 非公開クイズはオーナー以外アクセス不可
        if (!$quiz->is_public && $quiz->user_id !== Auth::id()) {
            abort(403, 'このクイズは非公開です。');
        }

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
     * 回答を受け取り、回答結果画面を表示する
     *
     * - 現在の問題を取得する
     * - 選択された choice_id がその問題の選択肢かを確認する
     * - 正誤判定を行う
     * - 回答内容と自信度を session に保存する
     * - 回答結果画面を表示する
     *
     * ※ この時点では DB 保存しない
     */
    public function answer(QuizPlayRequest $request, $id)
    {
        $quiz = Quiz::with(['questions.choices'])->findOrFail($id);

        // 非公開クイズはオーナー以外アクセス不可
        if (!$quiz->is_public && $quiz->user_id !== Auth::id()) {
            abort(403, 'このクイズは非公開です。');
        }

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

        return view('quiz_answer', [
            'quiz' => $quiz,
            'question' => $question,
            'selectedChoice' => $selectedChoice,
            'correctChoice' => $correctChoice,
            'isCorrect' => $isCorrect,
            'isLast' => $isLast,
            'step' => $step,
            'confidence' => $request->input('confidence'),
        ]);
    }

    /**
     * 次の問題へ遷移する
     *
     * - 現在の step をもとに次の step を計算する
     * - 次の問題がなければ結果画面へ遷移する
     */
    public function next(Request $request, $id)
    {
        $quiz = Quiz::with(['questions'])->findOrFail($id);

        // 非公開クイズはオーナー以外アクセス不可
        if (!$quiz->is_public && $quiz->user_id !== Auth::id()) {
            abort(403, 'このクイズは非公開です。');
        }

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
     * - session に保存された解答履歴から結果を組み立てる
     * - 問題ごとの正誤、回答内容、自信度を作成する
     * - 表示用スナップショットを session に保持する
     * - ログイン済みかつ未保存なら、この時点で DB に正式保存する
     *
     * ※ result 到達時のみ正式記録にする
     */
    public function result($id)
    {
        $quiz = Quiz::with(['questions.choices'])->findOrFail($id);

        // 非公開クイズはオーナー以外アクセス不可
        if (!$quiz->is_public && $quiz->user_id !== Auth::id()) {
            abort(403, 'このクイズは非公開です。');
        }

        $progress = session()->get("quiz_progress.$id", []);
        $questions = $this->getOrderedQuestions($quiz, $id);

        $score = 0;
        $resultDetails = [];

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
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'question_explanation' => $question->explanation,
                'selected_choice_id' => $selectedChoice?->id,
                'selected_answer' => $selectedChoice?->choice_text ?? '未回答',
                'correct_choice_id' => $correctChoice?->id,
                'correct_answer' => $correctChoice?->choice_text ?? '未設定',
                'confidence' => $confidence ?? '未回答',
                'is_correct' => $isCorrect,
            ];
        }

        $total = $questions->count();

        // 結果画面表示用のスナップショットを session に保持する
        // ゲストがあとでログインして保存する時にも使う
        session()->put("quiz_result_snapshot.$id", [
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total' => $total,
            'result_details' => $resultDetails,
        ]);

        // ログイン済みで、まだ今回の結果を保存していない場合のみ DB に保存する
        if (Auth::check() && !session()->has("quiz_result_saved.$id")) {
            try {
                $this->saveQuizResultToDb((int) $quiz->id);
                session()->put("quiz_result_saved.$id", true);
            } catch (\Throwable $e) {
                // DB保存に失敗してもリザルト画面は表示する（ログに記録）
                \Log::error('クイズ結果のDB保存に失敗しました', [
                    'quiz_id' => $quiz->id,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('quiz_result', [
            'quiz' => $quiz,
            'score' => $score,
            'total' => $total,
            'resultDetails' => $resultDetails,
            'canSaveResult' => !Auth::check(), // ゲスト時のみ保存導線を表示する想定
        ]);
    }

    /**
     * ゲストがログイン後に、result 画面で見ていた結果を DB に保存する
     *
     * - session に保持している result スナップショットを使う
     * - 保存済みなら重複保存しない
     * - 保存後は結果画面へ戻す
     */
    public function saveResultAfterLogin($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $snapshot = session()->get("quiz_result_snapshot.$id");

        if (!$snapshot) {
            return redirect()->route('quiz.start', $id)
                ->with('error', '保存対象の結果が見つかりませんでした。');
        }

        if (!session()->has("quiz_result_saved.$id")) {
            $this->saveQuizResultToDb((int) $id);
            session()->put("quiz_result_saved.$id", true);
        }

        return redirect()->route('quiz.result', $id)
            ->with('status', '結果を保存しました。');
    }

    /**
     * session に保持している結果スナップショットを DB に保存する
     *
     * - Score を1件作成する
     * - 各問題の結果を Answer として保存する
     * - confidence は confidence_level から confidence_id に変換する
     */
    private function saveQuizResultToDb(int $quizId): ?Score
    {
        if (!Auth::check()) {
            return null;
        }

        $snapshot = session()->get("quiz_result_snapshot.$quizId");

        if (!$snapshot) {
            return null;
        }

        $resultDetails = $snapshot['result_details'] ?? [];

        $score = Score::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quizId,
            'score_value' => $snapshot['score'] ?? 0,
            'answered_count' => collect($resultDetails)
                ->whereNotNull('selected_choice_id')
                ->count(),
            'correct_count' => $snapshot['score'] ?? 0,
        ]);

        foreach ($resultDetails as $detail) {
            // 未回答の問題は Answer レコードを作成しない（choice_id / confidence_id が非nullableのため）
            if ($detail['selected_choice_id'] === null) {
                continue;
            }

            // 自信度を解決する（DBに該当レコードがなければnull）
            $confidenceId = $this->resolveConfidenceId(
                ($detail['confidence'] ?? null) !== '未回答'
                    ? $detail['confidence']
                    : null
            );

            // confidence_id が解決できなかった場合もスキップ（NOT NULL制約を満たすため）
            if ($confidenceId === null) {
                continue;
            }

            Answer::create([
                'user_id' => Auth::id(),
                'question_id' => $detail['question_id'],
                'choice_id' => $detail['selected_choice_id'],
                'confidence_id' => $confidenceId,
                'score_id' => $score->id,
                'is_correct' => $detail['is_correct'],
            ]);
        }

        return $score;
    }

    /**
     * セッションに保存された順番で問題・選択肢を並び替えて返す
     *
     * - quiz_order で問題順を制御する
     * - quiz_choice_order で各問題の選択肢順を制御する
     * - セッション情報がない場合は元の順序を保険として使用する
     */
    private function getOrderedQuestions(Quiz $quiz, int|string $quizId)
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

    /**
     * confidence_level（high / medium / low）から
     * confidence テーブルの id を取得する
     */
    private function resolveConfidenceId(?string $confidenceValue): ?int
    {
        if (!$confidenceValue) {
            return null;
        }

        return Confidence::where('confidence_level', $confidenceValue)->value('id');
    }

    /**
     * ゲストユーザーが「ログインして保存 / 新規登録して保存」を押した時の導線
     *
     * - 保存先URLを intended として session に保存する
     * - mode が register なら新規登録画面へ、未指定ならログイン画面へ遷移する
     */
    public function prepareSaveAfterLogin(Request $request, $id)
    {
        $snapshot = session()->get("quiz_result_snapshot.$id");

        if (!$snapshot) {
            return redirect()->route('quiz.start', $id)
                ->with('error', '保存対象の結果が見つかりませんでした。');
        }

        // ログイン / 登録後に遷移させたい保存用URLを intended にセットする
        session()->put('url.intended', route('quiz.result.save', $id));

        if ($request->input('mode') === 'register') {
            return redirect()->route('register');
        }

        return redirect()->route('login');
    }
}
