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
        // 1. question_titles を参照している外部キーを削除する
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['title_id']);
        });

        Schema::table('question_title_categories', function (Blueprint $table) {
            $table->dropForeign(['title_id']);
        });

        Schema::table('scores', function (Blueprint $table) {
            $table->dropForeign(['title_id']);
        });

        // 2. テーブル名の変更
        Schema::rename('question_titles', 'quizzes');
        Schema::rename('question_title_categories', 'quiz_categories');

        // 3. カラム名の変更と外部キーの再設定
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('title_id', 'quiz_id');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('quiz_id')->references('id')->on('quizzes');
        });

        Schema::table('quiz_categories', function (Blueprint $table) {
            $table->renameColumn('title_id', 'quiz_id');
        });
        Schema::table('quiz_categories', function (Blueprint $table) {
            $table->foreign('quiz_id')->references('id')->on('quizzes');
        });

        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('title_id', 'quiz_id');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->foreign('quiz_id')->references('id')->on('quizzes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 外部キーの削除
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
        });
        Schema::table('quiz_categories', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
        });

        // テーブル名を元に戻す
        Schema::rename('quizzes', 'question_titles');
        Schema::rename('quiz_categories', 'question_title_categories');

        // カラム名と外部キーを元に戻す
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('quiz_id', 'title_id');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('title_id')->references('id')->on('question_titles');
        });

        Schema::table('question_title_categories', function (Blueprint $table) {
            $table->renameColumn('quiz_id', 'title_id');
        });
        Schema::table('question_title_categories', function (Blueprint $table) {
            $table->foreign('title_id')->references('id')->on('question_titles');
        });

        Schema::table('scores', function (Blueprint $table) {
            $table->renameColumn('quiz_id', 'title_id');
        });
        Schema::table('scores', function (Blueprint $table) {
            $table->foreign('title_id')->references('id')->on('question_titles');
        });
    }
};
