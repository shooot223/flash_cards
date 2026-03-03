<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/', function(){
    return view('top');
});

Route::middleware('auth')->group(function () {
    Route::get('/mypage');
});

require __DIR__.'/auth.php';
