<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\BrokerApiController;
use App\Http\Controllers\Api\LeadApiController;
use App\Http\Controllers\Api\SalesExecutiveApiController;
use App\Http\Controllers\Api\SiteVisitApiController;
use App\Http\Controllers\Api\SubscriptionApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REOS Sanctum API Routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');

Route::middleware(['auth:sanctum', 'subscription'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Company Subscription APIs (/api/subscription/*)
    Route::prefix('subscription')->group(function () {
        Route::get('/plans', [SubscriptionApiController::class, 'plans']);
        Route::get('/status', [SubscriptionApiController::class, 'status']);
        Route::post('/subscribe', [SubscriptionApiController::class, 'subscribe']);
        Route::post('/renew', [SubscriptionApiController::class, 'renew']);
    });

    // FCM Push Notification Token APIs
    Route::post('/fcm-token', [AuthController::class, 'updateFcmToken']);
    Route::delete('/fcm-token', [AuthController::class, 'removeFcmToken']);

    // AI Assistant APIs (/api/ai/*)
    Route::prefix('ai')->group(function () {
        Route::get('/lead-score/{id}', [\App\Http\Controllers\Api\AiAssistantApiController::class, 'leadScore']);
        Route::get('/recommendations/{id}', [\App\Http\Controllers\Api\AiAssistantApiController::class, 'recommendations']);
        Route::post('/summarize-call', [\App\Http\Controllers\Api\AiAssistantApiController::class, 'summarizeCall']);
        Route::get('/sales-coaching/{id}', [\App\Http\Controllers\Api\AiAssistantApiController::class, 'salesCoaching']);
        Route::get('/predictive-analytics', [\App\Http\Controllers\Api\AiAssistantApiController::class, 'predictiveAnalytics']);
    });

    // General Leads API (CRM Admin / Manager)
    Route::get('/leads', [LeadApiController::class, 'index']);
    Route::post('/leads', [LeadApiController::class, 'store']);
    Route::post('/leads/{lead}/status', [LeadApiController::class, 'updateStatus']);

    // General Site Visits API
    Route::get('/site-visits', [SiteVisitApiController::class, 'index']);
    Route::post('/site-visits', [SiteVisitApiController::class, 'store']);

    // General Bookings API
    Route::get('/bookings', [BookingApiController::class, 'index']);
    Route::post('/bookings', [BookingApiController::class, 'store']);
    Route::post('/bookings/{booking}/approve', [BookingApiController::class, 'approve']);

    // Support Ticket System API
    Route::get('/support/tickets', [\App\Http\Controllers\Api\SupportTicketApiController::class, 'index']);
    Route::post('/support/tickets', [\App\Http\Controllers\Api\SupportTicketApiController::class, 'store']);
    Route::get('/support/tickets/{id}', [\App\Http\Controllers\Api\SupportTicketApiController::class, 'show']);
    Route::post('/support/tickets/{id}/reply', [\App\Http\Controllers\Api\SupportTicketApiController::class, 'reply']);

    // Sales Executive Mobile App APIs (/api/sales/*)
    Route::prefix('sales')->group(function () {
        Route::get('/dashboard', [SalesExecutiveApiController::class, 'dashboard']);
        Route::get('/leads', [SalesExecutiveApiController::class, 'leads']);
        Route::post('/leads', [SalesExecutiveApiController::class, 'storeLead']);
        Route::get('/leads/{id}', [SalesExecutiveApiController::class, 'showLead']);
        Route::post('/leads/{id}/status', [SalesExecutiveApiController::class, 'updateLeadStatus']);
        Route::post('/leads/{id}/notes', [SalesExecutiveApiController::class, 'addNote']);
        Route::get('/leads/{id}/follow-ups', [SalesExecutiveApiController::class, 'followUps']);
        Route::post('/leads/{id}/follow-ups', [SalesExecutiveApiController::class, 'scheduleFollowUp']);
        Route::get('/site-visits', [SalesExecutiveApiController::class, 'siteVisits']);
        Route::post('/site-visits', [SalesExecutiveApiController::class, 'storeSiteVisit']);
        Route::post('/site-visits/{id}/status', [SalesExecutiveApiController::class, 'updateSiteVisitStatus']);
        Route::get('/projects', [SalesExecutiveApiController::class, 'projects']);
        Route::get('/projects/{id}/units', [SalesExecutiveApiController::class, 'projectUnits']);
        Route::get('/bookings', [SalesExecutiveApiController::class, 'bookings']);
        Route::post('/bookings', [SalesExecutiveApiController::class, 'createBooking']);
    });

    // Broker Subsystem APIs (/api/broker/*)
    Route::prefix('broker')->group(function () {
        Route::get('/dashboard', [BrokerApiController::class, 'dashboard']);
        Route::post('/leads', [BrokerApiController::class, 'submitLead']);
        Route::get('/leads', [BrokerApiController::class, 'leads']);
        Route::get('/leads/{id}', [BrokerApiController::class, 'show']);
        Route::get('/leads/{id}/timeline', [BrokerApiController::class, 'timeline']);
        Route::get('/leads/{id}/site-visits', [BrokerApiController::class, 'siteVisits']);
        Route::get('/leads/{id}/booking', [BrokerApiController::class, 'booking']);
        Route::get('/commissions', [BrokerApiController::class, 'commissions']);
        Route::get('/payouts', [BrokerApiController::class, 'payouts']);
        Route::get('/projects', [BrokerApiController::class, 'projects']);
        Route::get('/notifications', [BrokerApiController::class, 'notifications']);
        Route::post('/notifications/{id}/read', [BrokerApiController::class, 'markNotificationRead']);
    });
});
