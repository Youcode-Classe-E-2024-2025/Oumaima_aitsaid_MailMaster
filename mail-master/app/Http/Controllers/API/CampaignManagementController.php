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

    /**
     * Get statistics for a campaign.
     */
    public function getCampaignStats(Request $request, $id)
    {
        // Get newsletters belonging to the user
        $newsletterIds = Newsletter::where('user_id', $request->user()->id)
            ->pluck('id');
        
        $campaign = Campaign::whereIn('newsletter_id', $newsletterIds)
            ->where('id', $id)
            ->firstOrFail();

        // Calculate statistics
        $totalSubscribers = $campaign->newsletter->subscribers()->where('status', 'active')->count();
        $openedCount = $campaign->stats()->whereNotNull('opened_at')->count();
        $openRate = $totalSubscribers > 0 ? round(($openedCount / $totalSubscribers) * 100, 2) : 0;
        
        $clicksCount = $campaign->stats()->sum('click_count');
        $clickRate = $totalSubscribers > 0 ? round(($clicksCount / $totalSubscribers) * 100, 2) : 0;

        // Get most recent opens
        $recentOpens = $campaign->stats()
            ->whereNotNull('opened_at')
            ->with('subscriber:id,email,first_name,last_name')
            ->orderBy('opened_at', 'desc')
            ->take(10)
            ->get(['id', 'subscriber_id', 'opened_at']);

        return response()->json([
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'sent_at' => $campaign->sent_at,
            'total_subscribers' => $totalSubscribers,
            'opened_count' => $openedCount,
            'open_rate' => $openRate,
            'clicks_count' => $clicksCount,
            'click_rate' => $clickRate,
            'recent_opens' => $recentOpens,
        ]);
    }
}