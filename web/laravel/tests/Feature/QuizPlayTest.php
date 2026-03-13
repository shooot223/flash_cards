<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Choice;
use App\Models\Confidence;
use App\Models\Question;
use App\Models\QuestionTitle;
use App\Models\Score;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizPlayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // confidence マスタを用意
        Confidence::factory()->create([
            'id' => 1,
            'confidence_level' => 'high',
        ]);

        Confidence::factory()->create([
            'id' => 2,
            'confidence_level' => 'medium',
        ]);

        Confidence::factory()->create([
            'id' => 3,
            'confidence_level' => 'low',
        ]);
    }

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

        $this->assertEqualsCanonicalizing(
            $quiz->questions->pluck('id')->all(),
            $questionOrder
        );

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
     * 以前の session 情報が初期化されることを確認
     */
    public function test_start_clears_existing_quiz_sessions(): void
    {
        $quiz = $this->createQuizWithQuestions(2);

        session()->put("quiz_progress.{$quiz->id}", [
            999 => [
                'choice_id' => 1,
                'confidence' => 'high',
            ],
        ]);
        session()->put("quiz_result_snapshot.{$quiz->id}", ['dummy' => true]);
        session()->put("quiz_result_saved.{$quiz->id}", true);

        $this->get(route('quiz.start', $quiz->id))
            ->assertStatus(200);

        $this->assertSame([], session("quiz_progress.{$quiz->id}", []));
        $this->assertNull(session("quiz_result_snapshot.{$quiz->id}"));
        $this->assertNull(session("quiz_result_saved.{$quiz->id}"));
    }

    /**
     * play画面でセッションに保存された問題順に従って
     * 問題が表示されることを確認
     */
    public function test_play_displays_question_based_on_session_order(): void
    {
        $quiz = $this->createQuizWithQuestions(3);
        $quiz->load('questions.choices');

        $questionIds = $quiz->questions->pluck('id')->values()->all();
        $reversed = array_reverse($questionIds);

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
     * answer送信時に choice_id 未入力ならエラーになることを確認
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
     * answer送信時に confidence 未入力ならエラーになることを確認
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
     * 現在の問題に属さない choice_id を送ると
     * 不正な選択肢としてエラーになることを確認
     */
    public function test_answer_rejects_choice_that_does_not_belong_to_current_question(): void
    {
        $quiz = $this->createQuizWithQuestions(2);

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
     * 回答送信時に回答内容が session(progress) に保存されることを確認
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
     * result画面でスコア計算され、
     * snapshot が session に保存されることを確認
     */
    public function test_result_calculates_score_and_stores_snapshot_in_session(): void
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

        $snapshot = session("quiz_result_snapshot.{$quiz->id}");

        $this->assertIsArray($snapshot);
        $this->assertSame($quiz->id, $snapshot['quiz_id']);
        $this->assertSame(1, $snapshot['score']);
        $this->assertSame(2, $snapshot['total']);
        $this->assertCount(2, $snapshot['result_details']);
    }

    /**
     * ゲストユーザーが result を見ても
     * Score / Answer は DB 保存されないことを確認
     */
    public function test_guest_result_does_not_save_score_and_answers_to_database(): void
    {
        $quiz = $this->createQuizWithQuestions(1);
        $question = $quiz->questions->first();
        $correctChoice = $question->choices->firstWhere('is_correct', true);

        session()->put("quiz_order.{$quiz->id}", [$question->id]);
        session()->put("quiz_choice_order.{$quiz->id}", [
            $question->id => $question->choices->pluck('id')->all(),
        ]);
        session()->put("quiz_progress.{$quiz->id}", [
            $question->id => [
                'choice_id' => $correctChoice->id,
                'confidence' => 'high',
            ],
        ]);

        $this->get(route('quiz.result', $quiz->id))
            ->assertStatus(200);

        $this->assertDatabaseCount('scores', 0);
        $this->assertDatabaseCount('answers', 0);
    }

    /**
     * ログインユーザーが result を見ると
     * Score と Answer が DB 保存されることを確認
     */
    public function test_authenticated_user_result_saves_score_and_answers_to_database(): void
    {
        $user = User::factory()->create();
        $quiz = $this->createQuizWithQuestions(2);

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
                'confidence' => 'medium',
            ],
        ]);

        $this->actingAs($user)
            ->get(route('quiz.result', $quiz->id))
            ->assertStatus(200);

        $this->assertDatabaseHas('scores', [
            'user_id' => $user->id,
            'title_id' => $quiz->id,
            'score_value' => 1,
            'answered_count' => 2,
            'correct_count' => 1,
        ]);

        $score = Score::where('user_id', $user->id)
            ->where('title_id', $quiz->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($score);

        $this->assertDatabaseHas('answers', [
            'user_id' => $user->id,
            'score_id' => $score->id,
            'question_id' => $question1->id,
            'choice_id' => $correctChoice1->id,
            'is_correct' => true,
            'confidence_id' => Confidence::where('confidence_level', 'high')->value('id'),
        ]);

        $this->assertDatabaseHas('answers', [
            'user_id' => $user->id,
            'score_id' => $score->id,
            'question_id' => $question2->id,
            'choice_id' => $wrongChoice2->id,
            'is_correct' => false,
            'confidence_id' => Confidence::where('confidence_level', 'medium')->value('id'),
        ]);
    }

    /**
     * result を複数回開いても同一セッション内では二重保存されないことを確認
     */
    public function test_result_does_not_save_duplicate_records_in_same_session(): void
    {
        $user = User::factory()->create();
        $quiz = $this->createQuizWithQuestions(1);

        $question = $quiz->questions->first();
        $correctChoice = $question->choices->firstWhere('is_correct', true);

        session()->put("quiz_order.{$quiz->id}", [$question->id]);
        session()->put("quiz_choice_order.{$quiz->id}", [
            $question->id => $question->choices->pluck('id')->all(),
        ]);
        session()->put("quiz_progress.{$quiz->id}", [
            $question->id => [
                'choice_id' => $correctChoice->id,
                'confidence' => 'high',
            ],
        ]);

        $this->actingAs($user)->get(route('quiz.result', $quiz->id));
        $this->actingAs($user)->get(route('quiz.result', $quiz->id));

        $this->assertDatabaseCount('scores', 1);
        $this->assertDatabaseCount('answers', 1);
    }

    /**
     * ゲストが result 到達後にログインして saveResultAfterLogin を実行すると
     * DB に保存されることを確認
     */
    public function test_save_result_after_login_saves_guest_snapshot_to_database(): void
    {
        $user = User::factory()->create();
        $quiz = $this->createQuizWithQuestions(1);

        $question = $quiz->questions->first();
        $correctChoice = $question->choices->firstWhere('is_correct', true);

        session()->put("quiz_result_snapshot.{$quiz->id}", [
            'quiz_id' => $quiz->id,
            'score' => 1,
            'total' => 1,
            'result_details' => [
                [
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'selected_choice_id' => $correctChoice->id,
                    'selected_answer' => $correctChoice->choice_text,
                    'correct_choice_id' => $correctChoice->id,
                    'correct_answer' => $correctChoice->choice_text,
                    'confidence' => 'high',
                    'is_correct' => true,
                ],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('quiz.result.save', $quiz->id))
            ->assertRedirect(route('quiz.result', $quiz->id));

        $this->assertDatabaseHas('scores', [
            'user_id' => $user->id,
            'title_id' => $quiz->id,
            'score_value' => 1,
            'answered_count' => 1,
            'correct_count' => 1,
        ]);

        $score = Score::where('user_id', $user->id)
            ->where('title_id', $quiz->id)
            ->latest('id')
            ->first();

        $this->assertDatabaseHas('answers', [
            'user_id' => $user->id,
            'score_id' => $score->id,
            'question_id' => $question->id,
            'choice_id' => $correctChoice->id,
            'is_correct' => true,
            'confidence_id' => Confidence::where('confidence_level', 'high')->value('id'),
        ]);
    }

    /**
     * 保存対象 snapshot が無い状態で saveResultAfterLogin を呼ぶと
     * start 画面へ戻されることを確認
     */
    public function test_save_result_after_login_redirects_when_snapshot_does_not_exist(): void
    {
        $user = User::factory()->create();
        $quiz = $this->createQuizWithQuestions(1);

        $this->actingAs($user)
            ->get(route('quiz.result.save', $quiz->id))
            ->assertRedirect(route('quiz.start', $quiz->id));

        $this->assertDatabaseCount('scores', 0);
        $this->assertDatabaseCount('answers', 0);
    }

    private function createQuizWithQuestions(int $questionCount = 3): QuestionTitle
    {
        $quiz = QuestionTitle::factory()->create([
            'title' => 'テストクイズ',
            'description' => 'テスト用の説明',
            'is_public' => true,
        ]);

        for ($i = 1; $i <= $questionCount; $i++) {
            $question = Question::factory()->create([
                'title_id' => $quiz->id,
                'question_text' => "問題{$i}",
            ]);

            for ($j = 1; $j <= 4; $j++) {
                Choice::factory()->create([
                    'question_id' => $question->id,
                    'choice_text' => "問題{$i}の選択肢{$j}",
                    'is_correct' => $j === 1,
                ]);
            }
        }

        return $quiz->load('questions.choices');
    }
}
