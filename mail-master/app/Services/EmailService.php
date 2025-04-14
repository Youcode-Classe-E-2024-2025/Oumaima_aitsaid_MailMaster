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

    /**
     * Personalize content with subscriber data and add tracking pixel.
     *
     * @param string $content
     * @param Subscriber $subscriber
     * @param string $trackingPixel
     * @return string
     */
    protected function personalizeContent($content, $subscriber, $trackingPixel)
    {
        // Replace placeholders with subscriber data
        $personalized = str_replace(
            [
                '{{first_name}}', 
                '{{last_name}}', 
                '{{email}}',
                '{{full_name}}'
            ],
            [
                $subscriber->first_name ?? '',
                $subscriber->last_name ?? '',
                $subscriber->email,
                trim(($subscriber->first_name ?? '') . ' ' . ($subscriber->last_name ?? ''))
            ],
            $content
        );
        
        // Add invisible tracking pixel at the end of the email
        $trackingHtml = '<img src="' . $trackingPixel . '" alt="" width="1" height="1" style="display:none;" />';
        
        // Append tracking pixel before the closing body tag or at the end if no body tag
        if (strpos($personalized, '</body>') !== false) {
            $personalized = str_replace('</body>', $trackingHtml . '</body>', $personalized);
        } else {
            $personalized .= $trackingHtml;
        }
        
        return $personalized;
    }

    /**
     * Mock sending an email (for development purposes).
     * In production, you would use Laravel's Mail facade.
     *
     * @param string $to
     * @param string $subject
     * @param string $content
     * @return void
     */
    protected function mockSendEmail($to, $subject, $content)
    {
        // In a real app, you would use:
        /*
        Mail::raw($content, function ($message) use ($to, $subject) {
            $message->to($to)
                    ->subject($subject);
        });
        */
        
        // For development, just log it
        Log::info("Email would be sent to $to with subject: $subject");
    }
}