<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuizController;

Route::post('/quizzes', [QuizController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {

});
