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

   

   

   

}