<?php

namespace App\Providers;

use App\Events\QuizCreated;
use App\Listeners\LogQuizCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * アプリケーションサービスの登録
     */
    public function register(): void
    {
        //
    }

    /**
     * アプリケーションサービスの起動
     */
    public function boot(): void
    {
        // クイズ作成イベントにログリスナーを紐付ける
        Event::listen(
            QuizCreated::class,
            LogQuizCreated::class,
        );
    }
}
