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
        Schema::create('QuestionTitleCategory', function (Blueprint $table) {
            $table->id('question_title_category_id');
            $table->foreignId('category_id')->constrained('QuestionCategory', 'question_category_id');
            $table->foreignId('title_id')->constrained('QuestionTitle', 'title_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('QuestionTitleCategory');
    }
};
