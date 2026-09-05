<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CoApplicantController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\HrmsController;
use App\Http\Controllers\KycDocumentController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadImportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SiteVisitController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public Unauthenticated Project Showcase & Lead Capture for Buyers
Route::get('/p/{project}', [ProjectController::class, 'publicShow'])->name('projects.public');
Route::post('/p/{project}/inquire', [ProjectController::class, 'storePublicInquiry'])->name('projects.public.inquire');

Route::middleware(['auth', 'subscription'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/dashboard/seed-sample', [DashboardController::class, 'seedSampleData'])->name('dashboard.seed-sample');
    Route::post('/subscription/select-plan', [DashboardController::class, 'selectSubscriptionPlan'])->name('subscription.select-plan');
    Route::get('/admin/saas-subscriptions', [DashboardController::class, 'saasSubscriptions'])->name('admin.saas-subscriptions');
    Route::get('/admin/companies', [DashboardController::class, 'companiesListByFounder'])->name('admin.companies.index');
    Route::get('/admin/companies/create', [DashboardController::class, 'createCompanyFormByFounder'])->name('admin.companies.create');
    Route::post('/admin/companies/store', [DashboardController::class, 'storeCompanyByFounder'])->name('admin.companies.store');
    Route::get('/admin/companies/{company}', [DashboardController::class, 'showCompanyByFounder'])->name('admin.companies.show');
    Route::put('/admin/companies/{company}', [DashboardController::class, 'updateCompanyByFounder'])->name('admin.companies.update');
    Route::delete('/admin/companies/{company}', [DashboardController::class, 'destroyCompanyByFounder'])->name('admin.companies.destroy');
    Route::post('/admin/companies/{company}/subscription', [DashboardController::class, 'updateCompanySubscriptionByFounder'])->name('admin.companies.subscription');
    Route::post('/admin/saas-plans', [DashboardController::class, 'storeSubscriptionPlan'])->name('admin.saas-plans.store');
    Route::delete('/admin/saas-plans/{plan}', [DashboardController::class, 'destroySubscriptionPlan'])->name('admin.saas-plans.destroy');

    // Broker Portal & Channel Partners Directory
    Route::get('/brokers', [BrokerController::class, 'brokersDirectory'])->name('brokers.index');
    Route::post('/brokers', [BrokerController::class, 'storeBroker'])->name('brokers.store');
    Route::put('/brokers/{broker}', [BrokerController::class, 'updateBroker'])->name('brokers.update');
    Route::delete('/brokers/{broker}', [BrokerController::class, 'destroy'])->name('brokers.destroy');
    Route::post('/broker/submit-lead', [BrokerController::class, 'storeLead'])->name('broker.submit-lead');

    // Users & Team Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Profile & FCM
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/fcm-token', [\App\Http\Controllers\Api\AuthController::class, 'updateFcmToken'])->name('fcm.token.update');

    // Leads & Customers
    Route::get('/leads/export', [LeadController::class, 'exportExcel'])->name('leads.export');
    Route::post('/leads/import-csv', [LeadImportController::class, 'importCsv'])->name('leads.import-csv');
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
    Route::post('/leads/{lead}/call', [LeadController::class, 'logCall'])->name('leads.log-call');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/site-visits', [SiteVisitController::class, 'index'])->name('site-visits.index');

    // Projects & Inventory
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/buildings', [ProjectController::class, 'storeBuilding'])->name('projects.store-building');
    Route::post('/projects/{project}/units', [ProjectController::class, 'storeUnit'])->name('projects.store-unit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::delete('/units/{unit}', [ProjectController::class, 'destroyUnit'])->name('units.destroy');
    Route::put('/units/{unit}', [ProjectController::class, 'updateUnit'])->name('units.update');
    Route::post('/units/{unit}/status', [ProjectController::class, 'updateUnitStatus'])->name('units.update-status');

    // Bookings, Agreements, Payment Schedules & Co-Applicants
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking}/co-applicants', [CoApplicantController::class, 'store'])->name('bookings.co-applicants.store');
    Route::delete('/co-applicants/{coApplicant}', [CoApplicantController::class, 'destroy'])->name('co-applicants.destroy');
    Route::post('/bookings/{booking}/generate-schedules', [PaymentController::class, 'generateSchedules'])->name('bookings.generate-schedules');
    Route::get('/agreements', [AgreementController::class, 'index'])->name('agreements.index');
    Route::get('/agreements/{agreement}/file/{type}', [AgreementController::class, 'viewFile'])->name('agreements.file');
    Route::post('/agreements/{agreement}/upload', [AgreementController::class, 'uploadDocument'])->name('agreements.upload');
    Route::post('/agreements/{agreement}/skip', [BookingController::class, 'requestAgreementSkip'])->name('agreements.skip');
    Route::post('/agreements/{agreement}/approve-skip', [BookingController::class, 'approveAgreementSkip'])->name('agreements.approve-skip');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/schedules/{schedule}/demand-letter', [PaymentController::class, 'viewDemandLetter'])->name('payments.schedules.demand-letter');
    Route::get('/payments/{payment}/download-receipt', [PaymentController::class, 'downloadReceipt'])->name('payments.download-receipt');
    Route::get('/bookings/{booking}/download-receipt', [PaymentController::class, 'downloadBookingReceipt'])->name('bookings.download-receipt');
    Route::post('/bookings/{booking}/payment', [BookingController::class, 'recordPayment'])->name('bookings.payment');
    Route::post('/bookings/{booking}/process-payment', [BookingController::class, 'recordPayment'])->name('bookings.process-payment');

    // KYC & Document Vault
    Route::get('/documents', [KycDocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/kyc', [KycDocumentController::class, 'store'])->name('documents.kyc.store');
    Route::delete('/documents/{document}', [KycDocumentController::class, 'destroy'])->name('documents.destroy');

    // Brokers Directory & Ledger
    Route::get('/brokers', [BrokerController::class, 'brokersDirectory'])->name('brokers.index');
    Route::post('/brokers', [BrokerController::class, 'storeBroker'])->name('brokers.store');
    Route::get('/brokers/{broker}', [BrokerController::class, 'show'])->name('brokers.show');
    Route::delete('/brokers/{broker}', [BrokerController::class, 'destroy'])->name('brokers.destroy');

    // Module 16: Support Ticket System
    Route::get('/support-tickets', [\App\Http\Controllers\SupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::post('/support-tickets', [\App\Http\Controllers\SupportTicketController::class, 'store'])->name('support-tickets.store');
    Route::get('/support-tickets/{ticket}', [\App\Http\Controllers\SupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::post('/support-tickets/{ticket}/reply', [\App\Http\Controllers\SupportTicketController::class, 'reply'])->name('support-tickets.reply');
    Route::post('/support-tickets/{ticket}/status', [\App\Http\Controllers\SupportTicketController::class, 'updateStatus'])->name('support-tickets.update-status');
    Route::delete('/support-tickets/{ticket}', [\App\Http\Controllers\SupportTicketController::class, 'destroy'])->name('support-tickets.destroy');

    // Internal & Broker Single and Group Chat System
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/conversations', [ChatController::class, 'fetchConversations'])->name('chat.conversations');
    Route::get('/chat/{chat}/messages', [ChatController::class, 'fetchMessages'])->name('chat.messages');
    Route::post('/chat/{chat}/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/direct', [ChatController::class, 'startDirectChat'])->name('chat.direct');
    Route::post('/chat/group', [ChatController::class, 'createGroupChat'])->name('chat.group');

    // System Activity Audit Logs & HRMS Attendance Module
    Route::get('/hrms', [\App\Http\Controllers\HrmsController::class, 'index'])->name('hrms.index');
    Route::post('/hrms/clock-in', [\App\Http\Controllers\HrmsController::class, 'clockIn'])->name('hrms.clock-in');
    Route::post('/hrms/clock-out', [\App\Http\Controllers\HrmsController::class, 'clockOut'])->name('hrms.clock-out');
    Route::post('/hrms/leave-requests', [\App\Http\Controllers\HrmsController::class, 'storeLeaveRequest'])->name('hrms.leave-requests.store');
    Route::post('/hrms/leave-requests/{leaveRequest}/status', [\App\Http\Controllers\HrmsController::class, 'updateLeaveStatus'])->name('hrms.leave-requests.status');
    Route::post('/hrms/salary-slips', [\App\Http\Controllers\HrmsController::class, 'generateSalarySlip'])->name('hrms.salary-slips.store');

    // Operations, Analytics & System
    Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/broadcast', [NotificationController::class, 'sendBroadcast'])->name('notifications.broadcast');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/company-settings', [CompanySettingsController::class, 'index'])->name('company-settings.index');
    Route::put('/company-settings', [CompanySettingsController::class, 'update'])->name('company-settings.update');
});

require __DIR__.'/auth.php';
