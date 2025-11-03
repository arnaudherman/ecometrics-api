<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can issue a certificate.
     */
    public function test_user_can_issue_certificate(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        // Create some metrics
        $app->metrics()->create([
            'date' => '2025-11-01',
            'requests_count' => 100,
            'storage_gb' => 0.5,
            'cpu_hours' => 0.1,
        ]);

        $app->metrics()->create([
            'date' => '2025-11-02',
            'requests_count' => 100,
            'storage_gb' => 0.5,
            'cpu_hours' => 0.1,
        ]);

        $response = $this->withToken($token)
            ->postJson("/api/applications/{$app->id}/issue-certificate");

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Certificate issued successfully',
            ])
            ->assertJsonStructure([
                'certificate' => ['id', 'badge_level', 'issued_at', 'valid_until'],
                'stats',
            ]);

        $this->assertDatabaseHas('carbon_certificates', [
            'application_id' => $app->id,
        ]);
    }

    /**
     * Test user can view current certificate.
     */
    public function test_user_can_view_current_certificate(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        // Create metrics and issue certificate
        $app->metrics()->create([
            'date' => '2025-11-01',
            'requests_count' => 100,
            'storage_gb' => 0.5,
            'cpu_hours' => 0.1,
        ]);

        $this->withToken($token)
            ->postJson("/api/applications/{$app->id}/issue-certificate");

        // Get certificate
        $response = $this->withToken($token)
            ->getJson("/api/applications/{$app->id}/certificate");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'certificate' => ['id', 'badge_level', 'issued_at', 'valid_until'],
                'is_valid',
            ])
            ->assertJsonPath('is_valid', true);
    }

    /**
     * Test user can view certificate history.
     */
    public function test_user_can_view_certificate_history(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        // Create metrics
        $app->metrics()->create([
            'date' => '2025-11-01',
            'requests_count' => 100,
            'storage_gb' => 0.5,
            'cpu_hours' => 0.1,
        ]);

        // Issue 2 certificates
        $this->withToken($token)
            ->postJson("/api/applications/{$app->id}/issue-certificate");

        $this->withToken($token)
            ->postJson("/api/applications/{$app->id}/issue-certificate");

        // Get history
        $response = $this->withToken($token)
            ->getJson("/api/applications/{$app->id}/certificates");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'certificates');
    }

    /**
     * Test cannot issue certificate without metrics.
     */
    public function test_cannot_issue_certificate_without_metrics(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        $response = $this->withToken($token)
            ->postJson("/api/applications/{$app->id}/issue-certificate");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Not enough data to issue a certificate. Please add at least one metric.',
            ]);
    }
}