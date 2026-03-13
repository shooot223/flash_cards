<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\QuestionTitle;

class QuestionTitleFactory extends Factory
{
    protected $model = QuestionTitle::class;

    public function definition(): array
    {
        return [
            'title' => 'テスト問題',
            'description' => 'テスト説明',
            'user_id' => User::factory()->create()->id,
        ];
    }
}
