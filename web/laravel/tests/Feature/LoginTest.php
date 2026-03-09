<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    //テスト後に作製されたDB情報を削除
    use RefreshDatabase;

    //ログイン画面が開けるかどうか
    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('ログイン');
    }

    //ログイン成功のテスト、ログイン後にtopページにリダイレクト
    public function test_user_can_login()
    {
        //ユーザーを作成
        $user = User::factory()->create([
            'password' => bcrypt('password')//パスワードはハッシュ化された「password」
        ]);

        //loginにPOST送信（emailは作成されたメールアドレス、パスワードはpassword）
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        //ログイン成功したらマイページにリダイレクトされる
        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    //ログイン成功テスト、ログインが維持されているか動画
    public function test_user_is_authenticated_after_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    //パスワードミスでログイン失敗時のテスト
    public function test_login_fails_with_wrong_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors();
    }

    //必須項目未入力でログイン失敗テスト
    public function test_login_fails_when_email_and_password_are_empty()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    //ログインに失敗するとゲスト状態
    public function test_user_is_not_authenticated_with_wrong_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    //メールアドレスが異なるとログイン失敗（エラーが発生）
    public function test_login_faeils_with_wrong_email()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->post('/login', [
            'email' => 'aaa.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors();
    }

    //未ログイン状態でマイページにアクセスするとログイン画面に遷移
    public function test_guest_cannot_access_mypage()
    {
        $response = $this->get('/mypage');

        $response->assertRedirect('/login');
    }

    //ログイン済みユーザーはマイページにアクセスできる
    public function test_authenticated_user_can_access_mypage()
    {
        //ユーザーを作成
        $user = User::factory()->create();

        //作成したユーザーでログイン状態にしてマイページへアクセス
        //actingAs($user)：ログイン状態を偽装
        $response = $this->actingAs($user)->get('/mypage');

        //ステータスが200であれば成功
        $response->assertStatus(200);
    }
}
