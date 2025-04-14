<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $subscribers = Subscriber::query()
            ->when($request->has('search'), function ($query) use ($request) {
                return $query->where(function ($q) use ($request) {
                    $q->where('email', 'like', '%' . $request->search . '%')
                      ->orWhere('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->has('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($subscribers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'newsletter_ids' => 'nullable|array',
            'newsletter_ids.*' => 'exists:newsletters,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subscriber = Subscriber::create([
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'status' => 'active',
        ]);

        // Attach subscriber to newsletters if provided
        if ($request->has('newsletter_ids')) {
            $newsletters = Newsletter::whereIn('id', $request->newsletter_ids)
                ->where('user_id', $request->user()->id)
                ->get();
            
            $subscriber->newsletters()->attach($newsletters->pluck('id'));
        }

        return response()->json([
            'message' => 'Subscriber created successfully',
            'subscriber' => $subscriber
        ], 201);
    }

    public function show($id)
    {
        $subscriber = Subscriber::with('newsletters')->findOrFail($id);

        return response()->json($subscriber);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email,' . $id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,unsubscribed',
            'newsletter_ids' => 'nullable|array',
            'newsletter_ids.*' => 'exists:newsletters,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subscriber = Subscriber::findOrFail($id);

        $subscriber->update([
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'status' => $request->status ?? $subscriber->status,
        ]);

        // Update newsletter attachments if provided
        if ($request->has('newsletter_ids')) {
            $newsletters = Newsletter::whereIn('id', $request->newsletter_ids)
                ->where('user_id', $request->user()->id)
                ->get();
            
            $subscriber->newsletters()->sync($newsletters->pluck('id'));
        }

        return response()->json([
            'message' => 'Subscriber updated successfully',
            'subscriber' => $subscriber
        ]);
    }

    public function destroy($id)
    {
        $subscriber = Subscriber::findOrFail($id);
        $subscriber->delete();

        return response()->json([
            'message' => 'Subscriber deleted successfully'
        ]);
    }

    public function addToNewsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscriber_id' => 'required|exists:subscribers,id',
            'newsletter_id' => 'required|exists:newsletters,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newsletter = Newsletter::where('id', $request->newsletter_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $subscriber = Subscriber::findOrFail($request->subscriber_id);
        
        // Attach if not already attached
        if (!$subscriber->newsletters->contains($newsletter->id)) {
            $subscriber->newsletters()->attach($newsletter->id);
            return response()->json([
                'message' => 'Subscriber added to newsletter successfully'
            ]);
        }

        return response()->json([
            'message' => 'Subscriber is already in this newsletter'
        ]);
    }

    public function removeFromNewsletter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscriber_id' => 'required|exists:subscribers,id',
            'newsletter_id' => 'required|exists:newsletters,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newsletter = Newsletter::where('id', $request->newsletter_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
        
        $subscriber = Subscriber::findOrFail($request->subscriber_id);
        
        $subscriber->newsletters()->detach($newsletter->id);

        return response()->json([
            'message' => 'Subscriber removed from newsletter successfully'
        ]);
    }
}