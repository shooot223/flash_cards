<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    //ログアウトのアクセス先が存在する
    public function test_logout_route_exists()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
    }

    //ログアウト後挙動
    public function test_user_can_logout(): void
    {
        //ユーザーを作成
        $user = User::factory()->create();

        //ログインユーザーを偽装し、ログアウトにアクセス
        $response = $this->actingAs($user)->post('/logout');

        //ログアウト後の遷移先がトップページになっている
        $response->assertRedirect('/');
        //ログアウト後にゲスト（未ログイン）状態になっている
        $this->assertGuest();
    }

    //ログアウト後にマイページにアクセスできない
    public function test_user_cannot_access_mypage_after_logout(){

        //ユーザーを作成
        $user = User::factory()->create();
        //ログアウト処理
        $this->actingAs($user)->post('/logout');
        //マイページにアクセス
        $response = $this->get('/mypage');
        //ログインページにリダイレクト
        $response->assertRedirect('/login');
    }

}
