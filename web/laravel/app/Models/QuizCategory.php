<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'quiz_id',
    ];

    public function quizzes()
    {
        return $this->belongsToMany(
            Quiz::class,
            'quiz_categories',
            'category_id',
            'quiz_id'
        );
    }
}
