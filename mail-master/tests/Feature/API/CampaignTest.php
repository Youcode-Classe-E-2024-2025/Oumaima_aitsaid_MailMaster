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

   
    

   

  
}