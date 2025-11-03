<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can list their applications.
     */
    public function test_user_can_list_applications(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        Application::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->withToken($token)
            ->getJson('/api/applications');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'applications');
    }

    /**
     * Test user can create an application.
     */
    public function test_user_can_create_application(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/applications', [
                'name' => 'My Test App',
                'url' => 'https://test.example.com',
                'description' => 'Test description',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Application created successfully',
                'application' => [
                    'name' => 'My Test App',
                    'url' => 'https://test.example.com',
                ],
            ]);

        $this->assertDatabaseHas('applications', [
            'name' => 'My Test App',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test user can view their application.
     */
    public function test_user_can_view_application(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        $response = $this->withToken($token)
            ->getJson("/api/applications/{$app->id}");

        $response->assertStatus(200)
            ->assertJson([
                'application' => [
                    'id' => $app->id,
                    'name' => $app->name,
                ],
            ]);
    }

    /**
     * Test user can update their application.
     */
    public function test_user_can_update_application(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        $response = $this->withToken($token)
            ->putJson("/api/applications/{$app->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated description',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Application updated successfully',
                'application' => [
                    'name' => 'Updated Name',
                ],
            ]);

        $this->assertDatabaseHas('applications', [
            'id' => $app->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test user can delete their application.
     */
    public function test_user_can_delete_application(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $app = Application::factory()->create(['user_id' => $user->id]);

        $response = $this->withToken($token)
            ->deleteJson("/api/applications/{$app->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Application deleted successfully',
            ]);

        $this->assertDatabaseMissing('applications', [
            'id' => $app->id,
        ]);
    }

    /**
     * Test user cannot access other user's application.
     */
    public function test_user_cannot_access_other_users_application(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $token1 = $user1->createToken('test-token')->plainTextToken;
        
        $app = Application::factory()->create(['user_id' => $user2->id]);

        $response = $this->withToken($token1)
            ->getJson("/api/applications/{$app->id}");

        $response->assertStatus(403);
    }
}