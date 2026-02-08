<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Answer', function (Blueprint $table) {
            $table->id('answer_id');
            $table->foreignId('user_id')->constrained('User', 'user_id');
            $table->foreignId('question_id')->constrained('Question', 'question_id');
            $table->foreignId('choice_id')->constrained('Choice', 'choice_id');
            $table->foreignId('confidence_id')->constrained('Confidence', 'confidence_id');
            $table->foreignId('score_id')->constrained('Score', 'score_id');
            $table->boolean('is_correct');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Answer');
    }
};
