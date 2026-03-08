<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Score extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title_id',
        'score_value',
        'answered_count',
        'correct_count',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function questionTitle(){
        return $this->belongsTo(QuestionTitle::class, 'title_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
