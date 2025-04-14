<?php

namespace Tests\Feature\API;

use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriberTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $newsletter;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and generate a token
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        
        // Create a newsletter for the user
        $this->newsletter = Newsletter::factory()->create([
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function user_can_create_subscriber()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/subscribers', [
            'email' => 'subscriber@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'newsletter_ids' => [$this->newsletter->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'subscriber' => [
                    'id', 'email', 'first_name', 'last_name', 'status', 'created_at', 'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('subscribers', [
            'email' => 'subscriber@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'status' => 'active',
        ]);

        $subscriber = Subscriber::where('email', 'subscriber@example.com')->first();
        $this->assertTrue($subscriber->newsletters->contains($this->newsletter->id));
    }

   

   

 


}