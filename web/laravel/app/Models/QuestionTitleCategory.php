<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionTitleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title_id',
    ];

}
