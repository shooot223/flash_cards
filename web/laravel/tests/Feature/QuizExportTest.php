<?php

namespace Tests\Feature;

use App\Models\Choice;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class QuizExportTest extends TestCase
{
    use RefreshDatabase;

    //ログイン済みユーザーがcsvを出力することができる
    public function test_authenticated_user_can_export_selected_quizzes_as_csv()
    {
        $user = User::factory()->create();

        $quiz = Quiz::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post('/quiz/export_csv', [
            'quiz_ids' => [$quiz->id],
        ]);

        $response->assertStatus(200);
    }

    //未ログインユーザーはloginにリダイレクトされる
    public function test_guest_cannot_export_csv()
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->post('/quiz/export_csv', [
            'quiz_ids' => [$quiz->id],
        ]);

        $response->assertRedirect('/login');
    }

    //選択された問題だけが出力され、選択していない問題は出力されない
    public function test_only_selected_quizzes_are_exported()
    {
        $user = User::factory()->create();

        $quiz1 = Quiz::factory()->create([
            'user_id' => $user->id,
            'title' => '出力されるクイズ',
            'description' => '説明1',
        ]);

        $quiz2 = Quiz::factory()->create([
            'user_id' => $user->id,
            'title' => '出力されないクイズ',
            'description' => '説明2',
        ]);

        // quiz1 の問題と選択肢
        $question1 = Question::factory()->create([
            'quiz_id' => $quiz1->id,
            'question_text' => 'quiz1の問題文',
        ]);

        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'A', 'is_correct' => true]);
        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'B', 'is_correct' => false]);
        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'C', 'is_correct' => false]);
        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'D', 'is_correct' => false]);

        // quiz2 の問題と選択肢
        $question2 = Question::factory()->create([
            'quiz_id' => $quiz2->id,
            'question_text' => 'quiz2の問題文',
        ]);

        Choice::factory()->create(['question_id' => $question2->id, 'choice_text' => 'A2', 'is_correct' => true]);
        Choice::factory()->create(['question_id' => $question2->id, 'choice_text' => 'B2', 'is_correct' => false]);
        Choice::factory()->create(['question_id' => $question2->id, 'choice_text' => 'C2', 'is_correct' => false]);
        Choice::factory()->create(['question_id' => $question2->id, 'choice_text' => 'D2', 'is_correct' => false]);

        $response = $this->actingAs($user)->post(route('quiz.export.csv'), [
            'quiz_ids' => [$quiz1->id],
        ]);

        $response->assertStatus(200);

        $content = $response->streamedContent();

        // ヘッダー確認
        $this->assertStringContainsString('問題ID,問題タイトル,説明文,問題文,選択肢１,選択肢２,選択肢３,選択肢４,正解選択肢', $content);

        // 選択したクイズは出る
        $this->assertStringContainsString('出力されるクイズ', $content);
        $this->assertStringContainsString('quiz1の問題文', $content);

        // 選択していないクイズは出ない
        $this->assertStringNotContainsString('出力されないクイズ', $content);
        $this->assertStringNotContainsString('quiz2の問題文', $content);
    }

    //自分の作成した問題のみ出力可能
    public function test_only_own_quizzes_can_be_exported()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        //user1によるクイズ作成
        $quiz1 = Quiz::factory()->create([
            'user_id' => $user1->id,
            'title' => '出力されるクイズ',
            'description' => '説明1',
        ]);

        $question1 = Question::factory()->create([
            'quiz_id' => $quiz1->id,
            'question_text' => 'quiz1の問題文',
        ]);

        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'A', 'is_correct' => true]);
        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'B', 'is_correct' => false]);
        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'C', 'is_correct' => false]);
        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'D', 'is_correct' => false]);

        //user2によるクイズ作成
        $quiz2 = Quiz::factory()->create([
            'user_id' => $user2->id,
            'title' => '出力されるクイズ',
            'description' => '説明1',
        ]);

        $question1 = Question::factory()->create([
            'quiz_id' => $quiz2->id,
            'question_text' => 'quiz1の問題文',
        ]);

        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'A', 'is_correct' => true]);
        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'B', 'is_correct' => false]);
        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'C', 'is_correct' => false]);
        Choice::factory()->create(['question_id' => $question1->id, 'choice_text' => 'D', 'is_correct' => false]);

        $response = $this->actingAs($user1)->post(route('quiz.export.csv'), [
            'quiz_ids' => [$quiz1->id],
        ]);

        $response->assertStatus(200);

        $content = $response->streamedContent();
        // 選択したクイズは出る
        $this->assertStringContainsString('出力されるクイズ', $content);
        $this->assertStringContainsString('quiz1の問題文', $content);

        // 選択していないクイズは出ない
        $this->assertStringNotContainsString('出力されないクイズ', $content);
        $this->assertStringNotContainsString('quiz2の問題文', $content);
    }

    public function test_export_csv_requires_quiz_ids()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('quiz.export.csv'),
            []
        );

        $response->assertSessionHasErrors('quiz_ids');
    }

    public function test_export_csv_requires_quiz_ids_to_be_array()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('quiz.export.csv'),
            [
                'quiz_ids' => 1
            ]
        );

        $response->assertSessionHasErrors('quiz_ids');
    }

    public function test_export_csv_rejects_nonexistent_quiz_ids()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('quiz.export.csv'),
            [
                'quiz_ids' => [9999]
            ]
        );

        $response->assertSessionHasErrors('quiz_ids.0');
    }
}
