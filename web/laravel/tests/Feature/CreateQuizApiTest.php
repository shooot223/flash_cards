<?php

namespace Tests\Feature;

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

}
