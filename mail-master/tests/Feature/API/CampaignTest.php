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

    /** @test */
    public function user_can_schedule_campaign()
    {
        $campaign = Campaign::factory()->create([
            'newsletter_id' => $this->newsletter->id,
            'status' => 'draft',
        ]);

        $scheduledAt = now()->addDay()->format('Y-m-d H:i:s');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/campaigns/{$campaign->id}/schedule", [
            'scheduled_at' => $scheduledAt,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Campaign scheduled successfully',
                'campaign' => [
                    'id' => $campaign->id,
                    'status' => 'scheduled',
                ]
            ]);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'status' => 'scheduled',
        ]);
    }

    /** @test */
    public function user_cannot_update_sent_campaign()
    {
        $campaign = Campaign::factory()->create([
            'newsletter_id' => $this->newsletter->id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/campaigns/{$campaign->id}", [
            'name' => 'Updated Name',
            'subject' => 'Updated Subject',
            'content' => 'Updated Content',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Cannot update a campaign that has already been sent'
            ]);
    }

    /** @test */
    public function user_can_preview_campaign()
    {
        $campaign = Campaign::factory()->create([
            'newsletter_id' => $this->newsletter->id,
            'content' => '<p>Test content</p>',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/campaigns/{$campaign->id}/preview");

        $response->assertStatus(200)
            ->assertJsonStructure(['preview']);
    }
}