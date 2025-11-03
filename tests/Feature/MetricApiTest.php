<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Metric;
use App\Models\User;
use Database\Factories\MetricFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        MetricFactory::resetDateCounter(); // Reset pour chaque test
    }

    /**
     * Test user can create a metric for their application.
     */
    public function test_user_can_create_metric(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        $response = $this->withToken($token)
            ->postJson("/api/applications/{$app->id}/metrics", [
                'date' => '2025-11-02',
                'requests_count' => 1000,
                'storage_gb' => 1.0,
                'cpu_hours' => 0.5,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Metric created successfully',
            ])
            ->assertJsonPath('metric.carbon_footprint_kg', 0.5);

        $this->assertDatabaseCount('metrics', 1);
        $metric = Metric::first();
        $this->assertEquals($app->id, $metric->application_id);
        $this->assertEquals('2025-11-02', $metric->date->format('Y-m-d'));
    }

    /**
     * Test user can list metrics for their application.
     */
    public function test_user_can_list_metrics(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        // Crée 3 metrics avec des dates uniques
        Metric::factory()->count(3)->create(['application_id' => $app->id]);

        $response = $this->withToken($token)
            ->getJson("/api/applications/{$app->id}/metrics");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'metrics');
    }

    /**
     * Test user can get metrics stats.
     */
    public function test_user_can_get_metrics_stats(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        $app->metrics()->create([
            'date' => '2025-11-01',
            'requests_count' => 1000,
            'storage_gb' => 1.0,
            'cpu_hours' => 0.5,
        ]);

        $app->metrics()->create([
            'date' => '2025-11-02',
            'requests_count' => 2000,
            'storage_gb' => 2.0,
            'cpu_hours' => 1.0,
        ]);

        $response = $this->withToken($token)
            ->getJson("/api/applications/{$app->id}/metrics/stats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'stats' => [
                    'total_metrics',
                    'total_requests',
                    'total_storage_gb',
                    'total_cpu_hours',
                    'total_carbon_footprint_kg',
                    'average_carbon_footprint_kg',
                    'date_range',
                ],
            ])
            ->assertJsonPath('stats.total_metrics', 2)
            ->assertJsonPath('stats.total_requests', 3000);
    }

    /**
     * Test cannot create duplicate metric for same date.
     */
    public function test_cannot_create_duplicate_metric_for_same_date(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        // Create first metric via API
        $this->withToken($token)
            ->postJson("/api/applications/{$app->id}/metrics", [
                'date' => '2025-11-02',
                'requests_count' => 1000,
                'storage_gb' => 1.0,
                'cpu_hours' => 0.5,
            ]);

        // Try to create duplicate via API
        $response = $this->withToken($token)
            ->postJson("/api/applications/{$app->id}/metrics", [
                'date' => '2025-11-02',
                'requests_count' => 2000,
                'storage_gb' => 2.0,
                'cpu_hours' => 1.0,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors' => ['date']]);
    }
}