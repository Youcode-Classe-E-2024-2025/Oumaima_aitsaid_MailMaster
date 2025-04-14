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

    

  
    

    
}