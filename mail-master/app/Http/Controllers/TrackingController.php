<?php

namespace App\Http\Controllers;

use App\Models\CampaignStat;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Track email opens.
     */
    public function trackOpen(Request $request, $token)
    {
        // Find the campaign stat by tracking token
        $stat = CampaignStat::where('tracking_token', $token)->first();
        
        if ($stat) {
            // Update the opened_at timestamp if not set already
            if (!$stat->opened_at) {
                $stat->opened_at = now();
                $stat->save();
            }
            
            // Return a transparent 1x1 pixel
            return response()->make(
                base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 
                200, 
                ['Content-Type' => 'image/gif']
            );
        }
        
        // If token not found, return 404
        return response()->make(
            base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 
            404, 
            ['Content-Type' => 'image/gif']
        );
    }

    /**
     * Track link clicks.
     */
    public function trackClick(Request $request, $token)
    {
        // Find the campaign stat by tracking token
        $stat = CampaignStat::where('tracking_token', $token)->first();
        
        if ($stat) {
            // Increment click count
            $stat->click_count = ($stat->click_count ?? 0) + 1;
            
            // Set opened_at if not already set
            if (!$stat->opened_at) {
                $stat->opened_at = now();
            }
            
            $stat->save();
            
            // Redirect to the actual URL
            $url = $request->get('url', '/');
            return redirect()->away($url);
        }
        
        // If token not found, redirect to homepage
        return redirect('/');
    }
}