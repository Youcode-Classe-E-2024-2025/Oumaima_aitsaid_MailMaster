<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignStat;
use App\Models\Newsletter;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampaignManagementController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send a campaign to all subscribers.
     */
    public function sendCampaign(Request $request, $id)
    {
        // Get newsletters belonging to the user
        $newsletterIds = Newsletter::where('user_id', $request->user()->id)
            ->pluck('id');
        
        $campaign = Campaign::whereIn('newsletter_id', $newsletterIds)
            ->where('id', $id)
            ->firstOrFail();

        $result = $this->emailService->sendCampaign($campaign);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

   

   
}