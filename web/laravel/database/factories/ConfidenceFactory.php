<?php

namespace Database\Factories;

use App\Models\Confidence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Confidence>
 */
class ConfidenceFactory extends Factory
{
    protected $model = Confidence::class;

    public function definition(): array
    {
        return [
            'confidence_level' => $this->faker->randomElement([
                'high',
                'medium',
                'low',
            ]),
        ];
    }

    /**
     * high 固定
     */
    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'confidence_level' => 'high',
        ]);
    }

    /**
     * medium 固定
     */
    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'confidence_level' => 'medium',
        ]);
    }

    /**
     * low 固定
     */
    public function low(): static
    {
        return $this->state(fn (array $attributes) => [
            'confidence_level' => 'low',
        ]);
    }
}
