<?php

namespace Tests\Feature;

use App\Models\Choice;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateQuizApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_URL = 'api/quiz/store';
    private const CORRECT_DATA = [
        "title" => "Laravelクイズ2",
        "description" => "テスト2",
        "is_public" => true,
        "tags" => ["Laravel", "PHP"],
        "questions" => [
            [
                "question_text" => "Laravelのルートファイルは？",
                "choices" => ["あ", "い", "う", "え"],
                "correct_answer" => 1
            ],
            [
                "question_text" => "問題２",
                "choices" => ["a", "q", "c", "s"],
                "correct_answer" => 1
            ]
        ]
    ];

    private const CORRECT_RETURN_STRUCTURE = [
        'message',
        'data' => [
            'id',
            'title',
            'description',
            'is_public',
            'tags',
            'questions' => [
                '*' => [
                    'question_text',
                    'choices' => [
                        '*' => [
                            'choice_text',
                            'is_correct',
                        ],
                    ],
                ],
            ],
        ],
    ];
    //正常系
    //認証済みユーザーがクイズを作成できることをテスト
    public function test_authenticated_user_can_create_quiz(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(self::API_URL, self::CORRECT_DATA);

        $response->assertCreated()
            ->assertJsonStructure(self::CORRECT_RETURN_STRUCTURE);
    }

    //タグなしでも作成可能
    public function test_quiz_can_be_created_without_tags(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        unset($data['tags']);

        $response = $this->postJson(self::API_URL, $data);

        $response->assertCreated()
            ->assertJsonStructure(self::CORRECT_RETURN_STRUCTURE);
    }

    //認証系
    //未認証ユーザーは作成できない
    public function test_guest_cannot_create_quiz(): void
    {
        $response = $this->postJson(self::API_URL, self::CORRECT_DATA);

        $response->assertStatus(401);
    }

    //不正なトークンでは作成できない
    public function test_invalid_token_cannot_create_quiz(): void
    {
        $response = $this->postJson(self::API_URL, self::CORRECT_DATA, [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(401);
    }

    //バリデーション系
    //title 未指定でエラー
    public function test_title_is_required(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        unset($data['title']);

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //description 未指定でエラー
    public function test_description_is_required(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        unset($data['description']);

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //questions 未指定でエラー
    public function test_questions_is_required(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = self::CORRECT_DATA;
        unset($data['questions']);

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

     //questionsが空配列でエラー
    public function test_questions_cannot_be_empty(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['questions'] = [];

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //questions が配列でない場合エラー
    public function test_questions_must_be_an_array(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['questions'] = 'not an array';

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //question_text 未指定でエラー
    public function test_question_text_is_required(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        unset($data['questions'][0]['question_text']);

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //choices 未指定でエラー
    public function test_choices_is_required(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        unset($data['questions'][0]['choices']);

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //  choices が配列でない場合エラー
    public function test_choices_must_be_an_array(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['questions'][0]['choices'] = 'not an array';

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //choices が4つでない場合エラー
    public function test_choices_must_have_four_items(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['questions'][0]['choices'] = ['choice1', 'choice2', 'choice3'];

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //choiceが4つ以上の場合にエラー
    public function test_choices_cannot_have_more_than_four_items(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['questions'][0]['choices'] = ['choice1', 'choice2', 'choice3', 'choice4', 'choice5'];

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //choiceの中に空文字がある場合にエラー
    public function test_choices_cannot_contain_empty_strings(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['questions'][0]['choices'] = ['choice1', '', 'choice3', 'choice4'];

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //correct_answer 未指定でエラー
    public function test_correct_answer_is_required(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $data = self::CORRECT_DATA;
        unset($data['questions'][0]['correct_answer']);
        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);

    }

    //correct_answer が整数でない場合エラー
    public function test_correct_answer_must_be_an_integer(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['questions'][0]['correct_answer'] = 'not an integer';
        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //correct_answerが0~3でない場合エラー
    public function test_correct_answer_must_be_between_0_and_3(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['questions'][0]['correct_answer'] = 4;

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //tags が配列でない場合エラー
    public function test_tags_must_be_an_array(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['tags'] = 'not an array';

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //tags の要素が文字列でない場合エラー
    public function test_tags_must_contain_only_strings(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = self::CORRECT_DATA;
        $data['tags'] = [1, 2, 3];

        $response = $this->postJson(self::API_URL, $data);
        $response->assertStatus(422);
    }

    //DB保存確認
    //quizzes に保存される
    public function test_quizzes_are_saved(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(self::API_URL, self::CORRECT_DATA);
        $response->assertCreated();

        $quiz = Quiz::first();
        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id]);
    }

    //choices に保存される
    public function test_choices_are_saved(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(self::API_URL, self::CORRECT_DATA);
        $response->assertCreated();

        $quiz = Question::first();
        $this->assertDatabaseHas('choices', ['question_id' => $quiz->id]);
    }

    //correct_answer に対応する choice のみ is_correct=1
    public function test_correct_answer_is_saved_as_correct_choice(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(self::API_URL, self::CORRECT_DATA);
        $response->assertCreated();

        $questionData = self::CORRECT_DATA['questions'][0];
        $correctText = $questionData['choices'][$questionData['correct_answer']];

        $question = Question::first();

        $correctChoice = Choice::where('question_id', $question->id)
            ->where('choice_text', $correctText)
            ->first();

        $this->assertNotNull($correctChoice);
        $this->assertTrue($correctChoice->is_correct);
    }

    //tags が保存される
    public function test_tags_are_saved_for_quiz_title(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(self::API_URL, self::CORRECT_DATA);

        $response->assertCreated();

        $title = Quiz::first();
        $tagName = self::CORRECT_DATA['tags'][0];

        $category = QuestionCategory::where('category_name', $tagName)->first();

        $this->assertNotNull($category);

        $this->assertDatabaseHas('quiz_categories', [
            'quiz_id' => $title->id,
            'category_id' => $category->id,
        ]);
    }

}

