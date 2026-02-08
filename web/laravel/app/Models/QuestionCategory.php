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


    public function questionTitles(){
        return $this->belongsToMany(QuestionTitle::class, 'question_title_categories', 'category_id', 'title_id');
    }
}
