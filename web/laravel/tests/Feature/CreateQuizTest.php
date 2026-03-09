<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateQuizTest extends TestCase
{

    use RefreshDatabase;

    //quiz/createへのルートが通っているか
    public function test_create_quiz_route_exists()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('quiz/create');

        $response->assertStatus(200);
    }

    //quiz/confirmへのルートが通っているか
    public function test_confirm_quiz_route_exists()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/quiz/confirm', $this->correct_form);
        $response->assertStatus(200);
    }

    //quiz/storeへのルートが通っているか
    public function test_store_quiz_route_exists()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/quiz/store', $this->correct_form);
        $response->assertRedirect('quiz/complete');
    }

    //quiz/completeへのルートが通っているか
    public function test_complete_quiz_route_exists()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/quiz/complete');
        $response->assertStatus(200);
    }

    //ゲストでquiz/createにアクセスできない
    public function test_guest_cannot_access_create_quiz_route()
    {
        $response = $this->get('/quiz/create');
        $response->assertRedirect('/login');
    }

    //ゲストでquiz/confirmにアクセスできない
    public function test_guest_cannot_access_confirm_quiz_route()
    {
        $response = $this->post('/quiz/confirm', $this->correct_form);
        $response->assertRedirect('/login');

    }
    //ゲストだとstoreにアクセスできない
    public function test_guest_cannot_store_quiz()
    {
        $response = $this->post('/quiz/store', $this->correct_form);

        $response->assertRedirect('/login');
    }


    //正しい値がDBに保存される時
    public function test_user_can_store_quiz(){
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/quiz/store', $this->correct_form);

        $response->assertRedirect('quiz/complete');
        //保存ができているかの確認
        $this->assertDatabaseHas('question_titles', [
            'title' => 'テスト問題',
            'description' => 'テスト問題の説明',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('choices', [
            'choice_text' => 'Apple',
            'is_correct' => 1,
        ]);
    }

    //タイトルが未入力だと失敗
    public function test_user_cannot_confirm_quiz_without_title()
    {
        $user = User::factory()->create();

        $form = $this->correct_form;
        $form['title'] = '';

        $response = $this->actingAs($user)
            ->from('quiz/create')
            ->post('/quiz/confirm', $form);

        $response->assertRedirect('/quiz/create');
        $response->assertSessionHasErrors('title');
    }

    //説明が未入力だと失敗
    public function test_user_cannot_confirm_quiz_without_description(){
        $user = User::factory()->create();

        $form = $this->correct_form;
        $form['description'] = '';
        $response = $this->actingAs($user)
            ->from('quiz/create')
            ->post('/quiz/confirm', $form);
        $response->assertRedirect('/quiz/create');
        $response->assertSessionHasErrors('description');
    }

    //問題文が入力されていないときに失敗になる
    public function test_user_cannot_confirm_quiz_without_question(){
        $user = User::factory()->create();

        $form = $this->correct_form;
        $form['questions'][0]['question'] = '';
        $response = $this->actingAs($user)
            ->from('quiz/create')
            ->post('/quiz/confirm', $form);
        $response->assertRedirect('/quiz/create');
        $response->assertSessionHasErrors('questions.0.question');
    }

    //選択肢が入力されていない場合に失敗になる
    public function test_user_cannot_confirm_quiz_without_choices(){
        $user = User::factory()->create();
        $form = $this->correct_form;
        $form['questions'][0]['choices'] = [];
        $response = $this->actingAs($user)
            ->from('quiz/create')
            ->post('/quiz/confirm', $form);
        $response->assertRedirect('/quiz/create');
        $response->assertSessionHasErrors('questions.0.choices');
    }

    //正解の選択肢が選択されていない場合に失敗になる
    public function test_user_cannot_confirm_quiz_without_correct_choice(){
        $user = User::factory()->create();
        $form = $this->correct_form;
        $form['questions'][0]['correct'] = null;
        $response = $this->actingAs($user)
            ->from('quiz/create')
            ->post('/quiz/confirm', $form);
        $response->assertRedirect('/quiz/create');
        $response->assertSessionHasErrors('questions.0.correct');
    }

    //2問目移行で問題文だけ記入されている場合にその他の値が記入されていないと失敗する
    public function test_user_cannot_confirm_quiz_without_second_question()
    {
        $user = User::factory()->create();
        $form = $this->correct_form;
        $form['questions'][1]['question'] = '';
        $response = $this->actingAs($user)
            ->from('quiz/create')
            ->post('/quiz/confirm', $form);
        $response->assertRedirect('/quiz/create');
        $response->assertSessionHasErrors(['questions.1.question']);
    }

    public function test_user_cannot_confirm_quiz_with_second_choices(){
        $user = User::factory()->create();
        $form = $this->correct_form;
        $form['questions'][1]['choices'] = [];
        $response = $this->actingAs($user)
            ->from('quiz/create')
            ->post('/quiz/confirm', $form);
        $response->assertRedirect('/quiz/create');
        $response->assertSessionHasErrors(['questions.1.choices']);
    }

    public function test_user_cannot_confirm_quiz_with_second_correct(){
        $user = User::factory()->create();
        $form = $this->correct_form;
        $form['questions'][1]['correct'] = null;
        $response = $this->actingAs($user)
            ->from('quiz/create')
            ->post('/quiz/confirm', $form);
        $response->assertRedirect('/quiz/create');
        $response->assertSessionHasErrors(['questions.1.correct']);
    }


    private array $correct_form = [
        'title' => 'テスト問題',
        'description' => 'テスト問題の説明',
        'tags' => ['テストタグ'],
        'questions' => [
            [
                'question' => 'テスト問題の内容',
                'choices' => ['Apple', 'Banana', 'Orange', 'Pear'],
                'correct' => 0,
            ],
            [
                'question' => 'テスト問題の内容',
                'choices' => ['Apple', 'Banana', 'Orange', 'Pear'],
                'correct' =>   1,
            ]
        ],
    ];
}
