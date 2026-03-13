<?php

namespace Tests\Feature;

use App\Models\QuestionTitle;
use App\Models\Score;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizPlayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * クイズ開始時に
     * ・問題の出題順
     * ・選択肢の表示順
     * がランダムに生成されてセッションに保存されることを確認
     */
    public function test_start_stores_random_question_and_choice_order_in_session(): void
    {
        $quiz = $this->createQuizWithQuestions(3);

        $response = $this->get(route('quiz.start', $quiz->id));

        $response->assertStatus(200);

        $questionOrder = session("quiz_order.{$quiz->id}");
        $choiceOrders = session("quiz_choice_order.{$quiz->id}");

        $this->assertIsArray($questionOrder);
        $this->assertCount(3, $questionOrder);

        // 問題IDの集合が一致することを確認
        $this->assertEqualsCanonicalizing(
            $quiz->questions->pluck('id')->all(),
            $questionOrder
        );

        // 各問題の選択肢順が保存されていることを確認
        $this->assertIsArray($choiceOrders);

        foreach ($quiz->questions as $question) {
            $this->assertArrayHasKey($question->id, $choiceOrders);
            $this->assertCount(4, $choiceOrders[$question->id]);

            $this->assertEqualsCanonicalizing(
                $question->choices->pluck('id')->all(),
                $choiceOrders[$question->id]
            );
        }
    }

    /**
     * クイズ開始時に
     * 前回の回答履歴（progress）がリセットされることを確認
     */
    public function test_start_clears_existing_progress(): void
    {
        $quiz = $this->createQuizWithQuestions(2);

        session()->put("quiz_progress.{$quiz->id}", [
            999 => [
                'choice_id' => 1,
                'confidence' => 'high',
            ],
        ]);

        $this->get(route('quiz.start', $quiz->id))
            ->assertStatus(200);

        $this->assertSame([], session("quiz_progress.{$quiz->id}", []));
    }

    /**
     * play画面で
     * セッションに保存された問題順に従って
     * 問題が表示されることを確認
     */
    public function test_play_displays_question_based_on_session_order(): void
    {
        $quiz = $this->createQuizWithQuestions(3);
        $quiz->load('questions.choices');

        $questionIds = $quiz->questions->pluck('id')->values()->all();
        $reversed = array_reverse($questionIds);

        // 出題順を手動で設定
        $choiceOrders = [];

        foreach ($quiz->questions as $question) {
            $choiceOrders[$question->id] = $question->choices->pluck('id')->values()->all();
        }

        session()->put("quiz_order.{$quiz->id}", $reversed);
        session()->put("quiz_choice_order.{$quiz->id}", $choiceOrders);

        $expectedQuestion = $quiz->questions->firstWhere('id', $reversed[0]);

        $response = $this->get(route('quiz.play', [
            'id' => $quiz->id,
            'step' => 0,
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($expectedQuestion->question_text);
    }

    /**
     * 存在しない問題番号(step)が指定された場合
     * 結果画面へリダイレクトされることを確認
     */
    public function test_play_redirects_to_result_when_step_is_out_of_range(): void
    {
        $quiz = $this->createQuizWithQuestions(2);

        session()->put("quiz_order.{$quiz->id}", $quiz->questions->pluck('id')->all());

        $choiceOrders = [];

        foreach ($quiz->questions as $question) {
            $choiceOrders[$question->id] = $question->choices->pluck('id')->all();
        }

        session()->put("quiz_choice_order.{$quiz->id}", $choiceOrders);

        $response = $this->get(route('quiz.play', [
            'id' => $quiz->id,
            'step' => 999,
        ]));

        $response->assertRedirect(route('quiz.result', $quiz->id));
    }

    /**
     * 回答送信時に
     * choice_id が未入力の場合はバリデーションエラーになることを確認
     */
    public function test_answer_requires_choice_id(): void
    {
        $quiz = $this->createQuizWithQuestions(1);

        session()->put("quiz_order.{$quiz->id}", $quiz->questions->pluck('id')->all());

        $choiceOrders = [];

        foreach ($quiz->questions as $question) {
            $choiceOrders[$question->id] = $question->choices->pluck('id')->all();
        }

        session()->put("quiz_choice_order.{$quiz->id}", $choiceOrders);

        $response = $this->from(route('quiz.play', ['id' => $quiz->id, 'step' => 0]))
            ->post(route('quiz.answer', $quiz->id), [
                'step' => 0,
                'confidence' => 'high',
            ]);

        $response->assertRedirect(route('quiz.play', ['id' => $quiz->id, 'step' => 0]));
        $response->assertSessionHasErrors(['choice_id']);
    }

    /**
     * 回答送信時に
     * confidence が未入力の場合はバリデーションエラーになることを確認
     */
    public function test_answer_requires_confidence(): void
    {
        $quiz = $this->createQuizWithQuestions(1);

        $question = $quiz->questions->first();
        $choice = $question->choices->first();

        session()->put("quiz_order.{$quiz->id}", $quiz->questions->pluck('id')->all());

        $choiceOrders = [];

        foreach ($quiz->questions as $q) {
            $choiceOrders[$q->id] = $q->choices->pluck('id')->all();
        }

        session()->put("quiz_choice_order.{$quiz->id}", $choiceOrders);

        $response = $this->from(route('quiz.play', ['id' => $quiz->id, 'step' => 0]))
            ->post(route('quiz.answer', $quiz->id), [
                'step' => 0,
                'choice_id' => $choice->id,
            ]);

        $response->assertRedirect(route('quiz.play', ['id' => $quiz->id, 'step' => 0]));
        $response->assertSessionHasErrors(['confidence']);
    }

    /**
     * 現在の問題に属さない選択肢IDを送信した場合
     * 不正な選択肢としてエラーになることを確認
     */
    public function test_answer_rejects_choice_that_does_not_belong_to_current_question(): void
    {
        $quiz = $this->createQuizWithQuestions(2);

        $currentQuestion = $quiz->questions[0];
        $otherQuestion = $quiz->questions[1];

        $invalidChoice = $otherQuestion->choices->first();

        session()->put("quiz_order.{$quiz->id}", $quiz->questions->pluck('id')->values()->all());

        $choiceOrders = [];

        foreach ($quiz->questions as $question) {
            $choiceOrders[$question->id] = $question->choices->pluck('id')->values()->all();
        }

        session()->put("quiz_choice_order.{$quiz->id}", $choiceOrders);

        $response = $this->from(route('quiz.play', ['id' => $quiz->id, 'step' => 0]))
            ->post(route('quiz.answer', $quiz->id), [
                'step' => 0,
                'choice_id' => $invalidChoice->id,
                'confidence' => 'medium',
            ]);

        $response->assertRedirect(route('quiz.play', ['id' => $quiz->id, 'step' => 0]));
        $response->assertSessionHasErrors(['choice_id']);
    }

    /**
     * 回答が送信されたとき
     * 回答内容がセッション(progress)に保存されることを確認
     */
    public function test_answer_stores_progress_in_session(): void
    {
        $quiz = $this->createQuizWithQuestions(1);

        $question = $quiz->questions->first();
        $choice = $question->choices->firstWhere('is_correct', true);

        session()->put("quiz_order.{$quiz->id}", [$question->id]);

        session()->put("quiz_choice_order.{$quiz->id}", [
            $question->id => $question->choices->pluck('id')->all(),
        ]);

        $response = $this->post(route('quiz.answer', $quiz->id), [
            'step' => 0,
            'choice_id' => $choice->id,
            'confidence' => 'high',
        ]);

        $response->assertStatus(200);

        $progress = session("quiz_progress.{$quiz->id}");

        $this->assertArrayHasKey($question->id, $progress);
        $this->assertSame($choice->id, $progress[$question->id]['choice_id']);
        $this->assertSame('high', $progress[$question->id]['confidence']);
    }

    /**
     * result画面で
     * ・スコア計算
     * ・セッション削除
     * が正しく行われることを確認
     */
    public function test_result_calculates_score_and_clears_session(): void
    {
        $quiz = $this->createQuizWithQuestions(2);

        $quiz->load('questions.choices');

        $question1 = $quiz->questions[0];
        $question2 = $quiz->questions[1];

        $correctChoice1 = $question1->choices->firstWhere('is_correct', true);
        $wrongChoice2 = $question2->choices->firstWhere('is_correct', false);

        session()->put("quiz_order.{$quiz->id}", $quiz->questions->pluck('id')->values()->all());

        $choiceOrders = [];

        foreach ($quiz->questions as $question) {
            $choiceOrders[$question->id] = $question->choices->pluck('id')->values()->all();
        }

        session()->put("quiz_choice_order.{$quiz->id}", $choiceOrders);

        session()->put("quiz_progress.{$quiz->id}", [
            $question1->id => [
                'choice_id' => $correctChoice1->id,
                'confidence' => 'high',
            ],
            $question2->id => [
                'choice_id' => $wrongChoice2->id,
                'confidence' => 'low',
            ],
        ]);

        $response = $this->get(route('quiz.result', $quiz->id));

        $response->assertStatus(200);
        $response->assertViewHas('score', 1);
        $response->assertViewHas('total', 2);

        // セッションが削除されることを確認
        $this->assertNull(session("quiz_progress.{$quiz->id}"));
        $this->assertNull(session("quiz_order.{$quiz->id}"));
        $this->assertNull(session("quiz_choice_order.{$quiz->id}"));
    }
}
