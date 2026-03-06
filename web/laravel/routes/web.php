<?php

use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\TopController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});

//Route::get('/dashboard', function () {
//    return view('top');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/', [TopController::class, 'index'])->name('top');

Route::middleware('auth')->group(function () {
    Route::get('/mypage', [MypageController::class, 'display'])->name('mypage');
    Route::controller(QuizController::class)
        ->prefix('quiz')->name('quiz.')->group(function () {
            Route::match(['get', 'post'], '/create', 'create')->name('create');
            Route::post('/confirm', 'confirm')->name('confirm');
            Route::post('/store', 'store')->name('store');
            Route::get('/complete', 'complete')->name('complete');
            Route::get('/quiz/{id}/edit', 'edit')->name('edit');
            Route::put('/quiz/{id}', 'update')->name('update');
        });
});

require __DIR__.'/auth.php';
