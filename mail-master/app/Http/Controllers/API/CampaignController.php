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

    

  

 
}