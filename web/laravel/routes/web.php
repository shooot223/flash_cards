<?php

use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizPlayController;
use App\Http\Controllers\TopController;
use Illuminate\Support\Facades\Route;


//トップページ
Route::get('/', [TopController::class, 'index'])->name('top');

//問題回答
Route::controller(QuizPlayController::class)->prefix('quiz')->name('quiz.')->group(function () {
    Route::get('/{id}/start', 'start')->name('start');
    Route::get('/{id}/play', 'play')->name('play');
    Route::post('/{id}/answer', 'answer')->name('answer');
    Route::get('/{id}/result', 'result')->name('result');
    Route::post('/{id}/next', 'next')->name('next');
});

Route::middleware('auth')->group(function () {
    //マイページ
    Route::get('/mypage', [MypageController::class, 'display'])->name('mypage');

    //問題作成
    Route::controller(QuizController::class)->prefix('quiz')->name('quiz.')->group(function () {
        Route::match(['get', 'post'], '/create', 'create')->name('create');
        Route::post('/confirm', 'confirm')->name('confirm');
        Route::post('/store', 'store')->name('store');
        Route::get('/complete', 'complete')->name('complete');
        Route::get('/quiz/{id}/edit', 'edit')->name('edit');
        Route::put('/quiz/{id}', 'update')->name('update');
        Route::delete('/quiz/{id}', 'delete')->name('delete');
        Route::patch('/{id}/private', 'private')->name('private');
        Route::patch('/{id}/public', 'public')->name('public');
    });

    //プローフィール編集
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::patch('/password', 'updatePassword')->name('password.update');
        Route::post('/avatar', 'updateAvatar')->name('avatar.update');
        Route::delete('/avatar', 'destroyAvatar')->name('avatar.destroy');
    });
});

require __DIR__.'/auth.php';
