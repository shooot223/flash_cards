<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Confidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'confidence_level',
    ];


    public function answer()
    {
        return $this->hasOne(Answer::class);
    }
}
