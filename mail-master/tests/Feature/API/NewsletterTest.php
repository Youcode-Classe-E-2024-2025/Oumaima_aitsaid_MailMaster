<?php

namespace Tests\Feature\API;

use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and generate a token
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function user_can_create_newsletter()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/newsletters', [
            'name' => 'Test Newsletter',
            'description' => 'This is a test newsletter',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'newsletter' => [
                    'id', 'user_id', 'name', 'description', 'created_at', 'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('newsletters', [
            'user_id' => $this->user->id,
            'name' => 'Test Newsletter',
            'description' => 'This is a test newsletter',
        ]);
    }

    /** @test */
    public function user_can_view_their_newsletters()
    {
        // Create some newsletters for the user
        Newsletter::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/newsletters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'name', 'description', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_update_their_newsletter()
    {
        $newsletter = Newsletter::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/newsletters/{$newsletter->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Newsletter updated successfully',
                'newsletter' => [
                    'id' => $newsletter->id,
                    'name' => 'Updated Name',
                    'description' => 'Updated Description',
                ]
            ]);

        $this->assertDatabaseHas('newsletters', [
            'id' => $newsletter->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);
    }

    /** @test */
    public function user_can_delete_their_newsletter()
    {
        $newsletter = Newsletter::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/newsletters/{$newsletter->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Newsletter deleted successfully'
            ]);

        $this->assertDatabaseMissing('newsletters', [
            'id' => $newsletter->id
        ]);
    }

    /** @test */
    public function user_cannot_view_newsletters_of_other_users()
    {
        $otherUser = User::factory()->create();
        $otherNewsletter = Newsletter::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/newsletters/{$otherNewsletter->id}");

        $response->assertStatus(404);
    }
}