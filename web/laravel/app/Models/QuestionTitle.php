<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'is_public',
        'image_path',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(
            QuestionCategory::class,
            'question_title_categories',
            'title_id',
            'category_id'
        );
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'title_id');
    }

    public function scores(){
        return $this->hasMany(Score::class, 'title_id');
    }
}
