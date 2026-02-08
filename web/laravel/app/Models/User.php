<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function questionTitles(){
        return $this->hasMany(QuestionTitle::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }
}
