<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\NewsletterController;
use App\Http\Controllers\API\SubscriberController;
use App\Http\Controllers\API\
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('newsletters', NewsletterController::class);
    
    // Subscriber routes
    Route::apiResource('subscribers', SubscriberController::class);
    Route::post('/subscribers/add-to-newsletter', [SubscriberController::class, 'addToNewsletter']);
    Route::post('/subscribers/remove-from-newsletter', [SubscriberController::class, 'removeFromNewsletter']);
    
    // Campaign routes
    Route::apiResource('campaigns', CampaignController::class);
    Route::get('/campaigns/{id}/preview', [CampaignController::class, 'preview']);
});