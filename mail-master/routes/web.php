<?php

use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/tracking/open/{token}', [TrackingController::class, 'trackOpen'])->name('campaigns.track');
Route::get('/tracking/click/{token}', [TrackingController::class, 'trackClick'])->name('campaigns.track.click');
