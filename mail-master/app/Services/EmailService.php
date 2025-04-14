<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignStat;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailService
{
    /**
     * Send a campaign to all subscribers in the associated newsletter.
     *
     * @param Campaign $campaign
     * @return array
     */
    public function sendCampaign(Campaign $campaign)
    {
        // Check if campaign is in the correct status
        if ($campaign->status === 'sent') {
            return [
                'success' => false,
                'message' => 'This campaign has already been sent'
            ];
        }

        if ($campaign->status === 'draft') {
            return [
                'success' => false,
                'message' => 'Please schedule this campaign before sending'
            ];
        }

        // Get all active subscribers from the newsletter
        $subscribers = $campaign->newsletter->subscribers()
            ->where('status', 'active')
            ->get();

        if ($subscribers->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active subscribers found for this newsletter'
            ];
        }

        $sentCount = 0;
        $failedCount = 0;

        // Send to each subscriber
        foreach ($subscribers as $subscriber) {
            try {
                // Generate a unique tracking token for this subscriber/campaign
                $trackingToken = Str::uuid()->toString();
                
                // Create a tracking pixel URL
                $trackingPixel = route('campaigns.track', ['token' => $trackingToken]);
                
                // Prepare personalized content with tracking pixel
                $personalizedContent = $this->personalizeContent(
                    $campaign->content, 
                    $subscriber,
                    $trackingPixel
                );
                
                // Send the email (mock implementation for now)
                $this->mockSendEmail(
                    $subscriber->email,
                    $campaign->subject,
                    $personalizedContent
                );
                
                // Create stats record for tracking
                CampaignStat::create([
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'tracking_token' => $trackingToken,
                ]);
                
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Failed to send email to ' . $subscriber->email . ': ' . $e->getMessage());
                $failedCount++;
            }
        }

        // Update campaign status
        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => "Campaign sent successfully: $sentCount sent, $failedCount failed",
            'sent_count' => $sentCount,
            'failed_count' => $failedCount
        ];
    }

    

   
}