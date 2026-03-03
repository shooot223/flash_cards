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
        Schema::create('question_title_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('question_categories', 'id');
            $table->foreignId('title_id')->constrained('question_titles', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_title_categories');
    }
};
