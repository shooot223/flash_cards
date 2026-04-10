<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Quiz;
use Database\Factories\QuestionCategoryFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;

class TopTest extends TestCase
{
    use RefreshDatabase;

    //トップ画面にアクセスできるかのテスト
    public function test_top_page_access(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    //ヘルプ画面にアクセスできるかのテスト
    public function test_help_page_access(): void
    {
        $response = $this->get('/help');

        $response->assertStatus(200);
        $response->assertSeeText('使い方ガイド');
    }

    //公開問題のみ表示されるかのテスト
    public function test_only_public_questions_displayed(): void
    {
        $publicQuestion = Quiz::factory()->create([
            'title' => '公開問題',
            'is_public' => true
        ]);
        $privateQuestion = Quiz::factory()->create([
            'title' => '非公開問題',
            'is_public' => false
        ]);

        $response = $this->get('/');

        $response->assertSee($publicQuestion->title);
        $response->assertDontSee($privateQuestion->title);
    }

    //問題が新しい順で並ぶかのテスト
    public function test_questions_sorted_by_newest()
    {
        $oldQuestion = Quiz::factory()->create([
            'title' => '古い問題',
            'is_public' => true,
            'created_at' => now()->subDays(3),
        ]);

        $newQuestion = Quiz::factory()->create([
            'title' => '新しい問題',
            'is_public' => true,
            'created_at' => now(),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText('新しい問題');
        $response->assertSeeText('古い問題');
        $response->assertSeeTextInOrder(['新しい問題', '古い問題']);
    }

    //カテゴリ一覧が取得されているかのテスト
    public function test_categories_listed()
    {
        $category = QuestionCategory::factory()->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText($category->name);
    }

//キーワード検索: title に一致する場合のテスト
    public function test_keyword_search_matches_title()
    {
        Quiz::factory()->create([
            'title' => '特定のキーワードを含む問題',
            'is_public' => true,
        ]);

        Quiz::factory()->create([
            'title' => '別の問題',
            'is_public' => true,
        ]);

        $response = $this->get('/?keyword=特定のキーワード');

        $response->assertStatus(200);
        $response->assertSeeText('特定のキーワードを含む問題');
        $response->assertDontSeeText('別の問題');
    }

    //キーワード検索: description に一致する場合のテスト
    public function test_keyword_search_matches_description()
    {
        Quiz::factory()->create([
            'title' => 'テスト１',
            'description' => '特定のキーワードを含む説明',
        ]);

        Quiz::factory()->create([
            'title' => 'テスト２',
            'description' => '別の説明',
        ]);

        $response = $this->get('/?keyword=のキーワード');
        $response->assertStatus(200);
        $response->assertSeeText('テスト１');
        $response->assertDontSeeText('テスト２');
    }

    //タグ検索が正しく効くかのテスト
    public function test_category_filtering()
    {
        $targetCategory = QuestionCategory::factory()->create([
            'category_name' => '特定カテゴリ',
        ]);

        $otherCategory = QuestionCategory::factory()->create([
            'category_name' => '別カテゴリ',
        ]);

        $targetQuiz = Quiz::factory()->create([
            'title' => '特定カテゴリの問題',
            'is_public' => true,
        ]);

        $otherQuiz = Quiz::factory()->create([
            'title' => '別カテゴリの問題',
            'is_public' => true,
        ]);

        $targetQuiz->categories()->attach($targetCategory->id);
        $otherQuiz->categories()->attach($otherCategory->id);

        $response = $this->get('/?category=' . $targetCategory->id);

        $response->assertStatus(200);
        $response->assertSeeText('特定カテゴリの問題');
        $response->assertDontSeeText('別カテゴリの問題');
    }

    //キーワード検索とタグ検索の併用が正しく効くかのテスト
    public function test_keyword_and_category_filtering_combined()
    {
        $category1 = QuestionCategory::factory()->create(['category_name' => 'カテゴリ1']);
        $category2 = QuestionCategory::factory()->create(['category_name' => 'カテゴリ2']);

        $quiz1 = Quiz::factory()->create([
            'title' => 'キーワードを含む問題',
            'is_public' => true,
        ]);
        $quiz1->categories()->attach($category1->id);

        $quiz2 = Quiz::factory()->create([
            'title' => 'キーワードを含む問題',
            'is_public' => true,
        ]);
        $quiz2->categories()->attach($category2->id);

        $quiz3 = Quiz::factory()->create([
            'title' => '別のタイトル',
            'is_public' => true,
        ]);
        $quiz3->categories()->attach($category1->id);

        $response = $this->get('/?keyword=キーワード&category=' . $category1->id);

        $response->assertStatus(200);
        $response->assertSeeText('キーワードを含む問題');
        $response->assertDontSeeText('別のタイトル');
    }

    //Ajaxリクエスト時は quiz_list ビューが返る
    public function test_ajax_request_returns_quiz_list_view()
    {
        Quiz::factory()->create([
            'title' => 'Ajaxテスト問題',
            'is_public' => true,
        ]);

        $response = $this->get('/', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertStatus(200);

        $response->assertSeeText('Ajaxテスト問題');
    }
}
