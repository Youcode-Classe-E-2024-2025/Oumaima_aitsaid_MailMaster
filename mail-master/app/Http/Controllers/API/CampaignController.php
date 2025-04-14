<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $newsletterIds = Newsletter::where('user_id', $request->user()->id)
            ->pluck('id');
        
        $campaigns = Campaign::whereIn('newsletter_id', $newsletterIds)
            ->when($request->has('search'), function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('subject', 'like', '%' . $request->search . '%');
            })
            ->when($request->has('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($campaigns);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'newsletter_id' => 'required|exists:newsletters,id',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'nullable|in:draft,scheduled,sent',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newsletter = Newsletter::where('id', $request->newsletter_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $campaign = Campaign::create([
            'newsletter_id' => $request->newsletter_id,
            'name' => $request->name,
            'subject' => $request->subject,
            'content' => $request->content,
            'status' => $request->status ?? 'draft',
            'scheduled_at' => $request->scheduled_at,
        ]);

        return response()->json([
            'message' => 'Campaign created successfully',
            'campaign' => $campaign
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $newsletterIds = Newsletter::where('user_id', $request->user()->id)
            ->pluck('id');
        
        $campaign = Campaign::with('newsletter')
            ->whereIn('newsletter_id', $newsletterIds)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json($campaign);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'newsletter_id' => 'nullable|exists:newsletters,id',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'nullable|in:draft,scheduled,sent',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newsletterIds = Newsletter::where('user_id', $request->user()->id)
            ->pluck('id');
        
        $campaign = Campaign::whereIn('newsletter_id', $newsletterIds)
            ->where('id', $id)
            ->firstOrFail();

        if ($campaign->status === 'sent') {
            return response()->json([
                'message' => 'Cannot update a campaign that has already been sent'
            ], 403);
        }

        if ($request->has('newsletter_id')) {
            $newsletter = Newsletter::where('id', $request->newsletter_id)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        $campaign->update([
            'newsletter_id' => $request->newsletter_id ?? $campaign->newsletter_id,
            'name' => $request->name,
            'subject' => $request->subject,
            'content' => $request->content,
            'status' => $request->status ?? $campaign->status,
            'scheduled_at' => $request->scheduled_at ?? $campaign->scheduled_at,
        ]);

        return response()->json([
            'message' => 'Campaign updated successfully',
            'campaign' => $campaign
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $newsletterIds = Newsletter::where('user_id', $request->user()->id)
            ->pluck('id');
        
        $campaign = Campaign::whereIn('newsletter_id', $newsletterIds)
            ->where('id', $id)
            ->firstOrFail();

        if ($campaign->status === 'sent') {
            return response()->json([
                'message' => 'Cannot delete a campaign that has already been sent'
            ], 403);
        }

        $campaign->delete();

        return response()->json([
            'message' => 'Campaign deleted successfully'
        ]);
    }

 
}