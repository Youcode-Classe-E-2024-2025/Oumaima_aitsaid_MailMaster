<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Newsletter;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition()
    {
        return [
            'newsletter_id' => Newsletter::factory(),
            'name' => $this->faker->words(4, true),
            'subject' => $this->faker->sentence(),
            'content' => '<h1>' . $this->faker->sentence() . '</h1><p>' . $this->faker->paragraph(3) . '</p>',
            'status' => 'draft',
            'scheduled_at' => null,
            'sent_at' => null,
        ];
    }

   

    
}