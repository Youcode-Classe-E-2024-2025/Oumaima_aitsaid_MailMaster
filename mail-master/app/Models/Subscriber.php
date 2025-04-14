<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'status',
    ];

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class);
    }

    public function campaignStats()
    {
        return $this->hasMany(CampaignStat::class);
    }
}