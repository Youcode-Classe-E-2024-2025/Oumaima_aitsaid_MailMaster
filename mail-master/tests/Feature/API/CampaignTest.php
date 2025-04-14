<?php

namespace Tests\Feature\API;

use App\Models\Campaign;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
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
    public function user_can_create_campaign()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/campaigns', [
            'newsletter_id' => $this->newsletter->id,
            'name' => 'Test Campaign',
            'subject' => 'Test Subject',
            'content' => '<p>Hello {{first_name}},</p><p>This is a test email.</p>',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'campaign' => [
                    'id', 'newsletter_id', 'name', 'subject', 'content', 'status',
                    'created_at', 'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('campaigns', [
            'newsletter_id' => $this->newsletter->id,
            'name' => 'Test Campaign',
            'subject' => 'Test Subject',
            'status' => 'draft',
        ]);
    }

    

   

  
}