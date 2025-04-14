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

    /** @test */
    public function user_can_add_subscriber_to_newsletter()
    {
        $subscriber = Subscriber::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/subscribers/add-to-newsletter', [
            'subscriber_id' => $subscriber->id,
            'newsletter_id' => $this->newsletter->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Subscriber added to newsletter successfully'
            ]);

        $this->assertDatabaseHas('newsletter_subscriber', [
            'newsletter_id' => $this->newsletter->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }

    /** @test */
    public function user_can_remove_subscriber_from_newsletter()
    {
        $subscriber = Subscriber::factory()->create();
        $subscriber->newsletters()->attach($this->newsletter->id);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/subscribers/remove-from-newsletter', [
            'subscriber_id' => $subscriber->id,
            'newsletter_id' => $this->newsletter->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Subscriber removed from newsletter successfully'
            ]);

        $this->assertDatabaseMissing('newsletter_subscriber', [
            'newsletter_id' => $this->newsletter->id,
            'subscriber_id' => $subscriber->id,
        ]);
    }

    /** @test */
    public function user_can_update_subscriber()
    {
        $subscriber = Subscriber::factory()->create([
            'email' => 'original@example.com',
            'first_name' => 'Original',
            'last_name' => 'Name',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/subscribers/{$subscriber->id}", [
            'email' => 'updated@example.com',
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'status' => 'active',
        ]);

       // Suite de la fonction test dans SubscriberTest.php
       $response->assertStatus(200)
       ->assertJson([
           'message' => 'Subscriber updated successfully',
           'subscriber' => [
               'id' => $subscriber->id,
               'email' => 'updated@example.com',
               'first_name' => 'Updated',
               'last_name' => 'Name',
               'status' => 'active',
           ]
       ]);

   $this->assertDatabaseHas('subscribers', [
       'id' => $subscriber->id,
       'email' => 'updated@example.com',
       'first_name' => 'Updated',
       'last_name' => 'Name',
   ]);
}

/** @test */
public function user_can_delete_subscriber()
{
   $subscriber = Subscriber::factory()->create();

   $response = $this->withHeaders([
       'Authorization' => 'Bearer ' . $this->token,
   ])->deleteJson("/api/subscribers/{$subscriber->id}");

   $response->assertStatus(200)
       ->assertJson([
           'message' => 'Subscriber deleted successfully'
       ]);

   $this->assertDatabaseMissing('subscribers', [
       'id' => $subscriber->id
   ]);
}
}