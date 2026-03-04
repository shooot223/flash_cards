<?php

use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});

//Route::get('/dashboard', function () {
//    return view('top');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/', function(){
    return view('top');
})->name('top');

Route::middleware('auth')->group(function () {
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage');
});

require __DIR__.'/auth.php';
