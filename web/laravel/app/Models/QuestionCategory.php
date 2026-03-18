<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
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
