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

    /**
     * Schedule a campaign for future sending.
     */
    public function scheduleCampaign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get newsletters belonging to the user
        $newsletterIds = Newsletter::where('user_id', $request->user()->id)
            ->pluck('id');
        
        $campaign = Campaign::whereIn('newsletter_id', $newsletterIds)
            ->where('id', $id)
            ->firstOrFail();

        if ($campaign->status === 'sent') {
            return response()->json([
                'message' => 'Cannot schedule a campaign that has already been sent'
            ], 400);
        }

        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => $request->scheduled_at,
        ]);

        return response()->json([
            'message' => 'Campaign scheduled successfully',
            'campaign' => $campaign
        ]);
    }

   
}