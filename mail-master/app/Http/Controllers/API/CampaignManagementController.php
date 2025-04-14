<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignStat;
use App\Models\Newsletter;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
/**
 * @group Campaign Management
 *
 * API endpoints for managing campaign sending and statistics
 */

class CampaignManagementController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

      /**
     * Send a campaign
     *
     * Sends the campaign to all active subscribers in the associated newsletter.
     *
     * @urlParam id required The ID of the campaign to send. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Campaign sent successfully: 10 sent, 0 failed",
     *   "sent_count": 10,
     *   "failed_count": 0
     * }
     *
     * @response 400 {
     *   "success": false,
     *   "message": "This campaign has already been sent"
     * }
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
     * Schedule a campaign
     *
     * Schedule a campaign for future sending.
     *
     * @urlParam id required The ID of the campaign to schedule. Example: 1
     * @bodyParam scheduled_at datetime required The date and time to send the campaign. Example: 2023-05-15 10:00:00
     *
     * @response 200 {
     *   "message": "Campaign scheduled successfully",
     *   "campaign": {
     *     "id": 1,
     *     "newsletter_id": 1,
     *     "name": "Example Campaign",
     *     "subject": "Important Announcement",
     *     "status": "scheduled",
     *     "scheduled_at": "2023-05-15T10:00:00.000000Z",
     *     "updated_at": "2023-04-07T12:00:00.000000Z",
     *     "created_at": "2023-04-07T12:00:00.000000Z"
     *   }
     * }
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
     * Get campaign statistics
     *
     * Retrieve statistics for a specific campaign.
     *
     * @urlParam id required The ID of the campaign. Example: 1
     *
     * @response 200 {
     *   "campaign_id": 1,
     *   "campaign_name": "Example Campaign",
     *   "sent_at": "2023-04-07T12:00:00.000000Z",
     *   "total_subscribers": 100,
     *   "opened_count": 75,
     *   "open_rate": 75,
     *   "clicks_count": 25,
     *   "click_rate": 25,
     *   "recent_opens": [
     *     {
     *       "id": 10,
     *       "subscriber_id": 20,
     *       "opened_at": "2023-04-07T15:30:00.000000Z",
     *       "subscriber": {
     *         "id": 20,
     *         "email": "example@example.com",
     *         "first_name": "John",
     *         "last_name": "Doe"
     *       }
     *     }
     *   ]
     * }
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