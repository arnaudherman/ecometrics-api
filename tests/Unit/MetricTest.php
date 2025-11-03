<?php

namespace Tests\Unit;

use App\Models\Metric;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MetricTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test carbon footprint calculation.
     */
    public function test_carbon_footprint_is_calculated_correctly(): void
    {
        $metric = new Metric([
            'requests_count' => 10000,
            'storage_gb' => 5.5,
            'cpu_hours' => 2.3,
        ]);
        
        // (10000 * 0.0002) + (5.5 * 0.05) + (2.3 * 0.5) = 3.425
        $this->assertEquals(3.425, $metric->calculateCarbonFootprint());
    }

    /**
     * Test carbon footprint with zero values.
     */
    public function test_carbon_footprint_with_zero_values(): void
    {
        $metric = new Metric([
            'requests_count' => 0,
            'storage_gb' => 0,
            'cpu_hours' => 0,
        ]);

        $this->assertEquals(0, $metric->calculateCarbonFootprint());
    }

    /**
     * Test carbon footprint is calculated on creation.
     */
    public function test_carbon_footprint_calculated_automatically(): void
    {
        $user = \App\Models\User::factory()->create();
        $application = \App\Models\Application::factory()->create(['user_id' => $user->id]);

        $metric = $application->metrics()->create([
            'date' => '2025-11-02',
            'requests_count' => 1000,
            'storage_gb' => 1.0,
            'cpu_hours' => 0.5,
        ]);

        // (1000 * 0.0002) + (1.0 * 0.05) + (0.5 * 0.5) = 0.5
        $this->assertEquals(0.5, $metric->carbon_footprint_kg);
    }
}