<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\BrokerApiController;
use App\Http\Controllers\Api\LeadApiController;
use App\Http\Controllers\Api\SalesExecutiveApiController;
use App\Http\Controllers\Api\SiteVisitApiController;
use App\Http\Controllers\Api\SubscriptionApiController;
use App\Http\Controllers\Api\LeadSourceWebhookController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ManagerTeamApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\FollowUpApiController;
use App\Http\Controllers\Api\DocumentApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REOS Sanctum API Routes
|--------------------------------------------------------------------------
*/

// Public Inbound Lead Integration Webhook Endpoints
Route::get('/webhooks/lead-sources/{type}/{token}', [LeadSourceWebhookController::class, 'verify']);
Route::post('/webhooks/lead-sources/{type}/{token}', [LeadSourceWebhookController::class, 'handle']);


Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');

Route::middleware(['auth:sanctum', 'subscription', 'mobile.role'])->group(function () {
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
    Route::get('/attendance', [AttendanceApiController::class, 'index']);
    Route::post('/attendance/clock-in', [AttendanceApiController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceApiController::class, 'clockOut']);
    Route::get('/follow-ups', [FollowUpApiController::class, 'index']);
    Route::patch('/follow-ups/{id}/status', [FollowUpApiController::class, 'updateStatus']);
    Route::get('/documents', [DocumentApiController::class, 'index']);
    Route::post('/documents', [DocumentApiController::class, 'store']);
    Route::delete('/documents/{id}', [DocumentApiController::class, 'destroy']);
    Route::get('/reports/summary', [ReportApiController::class, 'summary']);

    // Database-backed notifications shared with the web notification feed
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);

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
    Route::post('/bookings/{booking}/reject', [BookingApiController::class, 'reject']);

    // Support Ticket System API
    Route::get('/support/tickets', [\App\Http\Controllers\Api\SupportTicketApiController::class, 'index']);
    Route::post('/support/tickets', [\App\Http\Controllers\Api\SupportTicketApiController::class, 'store']);
    Route::get('/support/tickets/{id}', [\App\Http\Controllers\Api\SupportTicketApiController::class, 'show']);
    Route::post('/support/tickets/{id}/reply', [\App\Http\Controllers\Api\SupportTicketApiController::class, 'reply']);
    Route::patch('/support/tickets/{id}/status', [\App\Http\Controllers\Api\SupportTicketApiController::class, 'updateStatus']);

    // Team chat APIs shared with the web chat authorization flow
    Route::get('/chat/conversations', [ChatController::class, 'fetchConversations']);
    Route::get('/chat/{chat}/messages', [ChatController::class, 'fetchMessages']);
    Route::post('/chat/{chat}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/chat/direct', [ChatController::class, 'startDirectChat']);
    Route::post('/chat/group', [ChatController::class, 'createGroupChat']);

    // Sales Executive Mobile App APIs (/api/sales/*)
    Route::prefix('sales')->middleware('mobile.sales')->group(function () {
        Route::get('/dashboard', [SalesExecutiveApiController::class, 'dashboard']);
        Route::get('/leads', [SalesExecutiveApiController::class, 'leads']);
        Route::post('/leads', [SalesExecutiveApiController::class, 'storeLead']);
        Route::post('/leads/check-duplicate', [SalesExecutiveApiController::class, 'checkDuplicate']);
        Route::get('/leads/{id}', [SalesExecutiveApiController::class, 'showLead']);
        Route::post('/leads/{id}/status', [SalesExecutiveApiController::class, 'updateLeadStatus']);
        Route::post('/leads/{id}/notes', [SalesExecutiveApiController::class, 'addNote']);
        Route::post('/leads/{id}/calls', [SalesExecutiveApiController::class, 'logCall']);
        Route::get('/leads/{id}/follow-ups', [SalesExecutiveApiController::class, 'followUps']);
        Route::post('/leads/{id}/follow-ups', [SalesExecutiveApiController::class, 'scheduleFollowUp']);
        Route::get('/site-visits', [SalesExecutiveApiController::class, 'siteVisits']);
        Route::post('/site-visits', [SalesExecutiveApiController::class, 'storeSiteVisit']);
        Route::post('/site-visits/{id}/status', [SalesExecutiveApiController::class, 'updateSiteVisitStatus']);
        Route::get('/projects', [SalesExecutiveApiController::class, 'projects']);
        Route::get('/projects/{id}/units', [SalesExecutiveApiController::class, 'projectUnits']);
        Route::get('/bookings', [SalesExecutiveApiController::class, 'bookings']);
        Route::post('/bookings', [SalesExecutiveApiController::class, 'createBooking']);
        Route::post('/bookings/{id}/payments', [SalesExecutiveApiController::class, 'recordPayment']);
        Route::post('/bookings/{id}/skip-agreement-request', [SalesExecutiveApiController::class, 'requestAgreementSkip']);
    });

    // Manager Mobile App APIs (/api/manager/*)
    // These use the same controller, but expose an explicit manager namespace
    // for React Native clients and return team-scoped data for manager users.
    Route::prefix('manager')->middleware('mobile.manager')->group(function () {
        Route::get('/dashboard', [SalesExecutiveApiController::class, 'dashboard']);
        Route::get('/team', [ManagerTeamApiController::class, 'index']);
        Route::post('/team/executives', [ManagerTeamApiController::class, 'store']);
        Route::put('/team/executives/{id}', [ManagerTeamApiController::class, 'update']);
        Route::patch('/team/executives/{id}/status', [ManagerTeamApiController::class, 'updateStatus']);
        Route::get('/leads', [SalesExecutiveApiController::class, 'leads']);
        Route::get('/leads/{id}', [SalesExecutiveApiController::class, 'showLead']);
        Route::post('/leads/{id}/status', [SalesExecutiveApiController::class, 'updateLeadStatus']);
        Route::get('/site-visits', [SalesExecutiveApiController::class, 'siteVisits']);
        Route::post('/site-visits/{id}/status', [SalesExecutiveApiController::class, 'updateSiteVisitStatus']);
        Route::get('/projects', [SalesExecutiveApiController::class, 'projects']);
        Route::get('/projects/{id}/units', [SalesExecutiveApiController::class, 'projectUnits']);
        Route::get('/bookings', [SalesExecutiveApiController::class, 'bookings']);
    });

    // Broker Subsystem APIs (/api/broker/*)
    Route::prefix('broker')->group(function () {
        Route::get('/dashboard', [BrokerApiController::class, 'dashboard']);
        Route::get('/profile', [BrokerApiController::class, 'profile']);
        Route::post('/bank-details', [BrokerApiController::class, 'updateBankDetails']);
        Route::post('/payout-request', [BrokerApiController::class, 'requestPayout']);
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
