<?php

namespace Database\Factories;

use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Metric>
 */
class MetricFactory extends Factory
{
    private static $dateCounter = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Génère des dates séquentielles pour éviter les doublons
        $date = now()->subDays(self::$dateCounter++)->format('Y-m-d');

        return [
            'application_id' => Application::factory(),
            'date' => $date,
            'requests_count' => fake()->numberBetween(100, 10000),
            'storage_gb' => fake()->randomFloat(2, 0.5, 10),
            'cpu_hours' => fake()->randomFloat(2, 0.1, 5),
        ];
    }

    /**
     * Reset the date counter (useful for tests).
     */
    public static function resetDateCounter(): void
    {
        self::$dateCounter = 0;
    }
}