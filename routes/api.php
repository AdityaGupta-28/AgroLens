<?php

use App\Http\Controllers\Api\V1\AnalyticsApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/analytics/dashboard', [AnalyticsApiController::class, 'dashboard'])
        ->middleware('permission:view_api');
});
