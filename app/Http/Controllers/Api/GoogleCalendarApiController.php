<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleCalendarApiController extends Controller
{
    protected GoogleCalendarService $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Store the Google OAuth server auth code received from the mobile app.
     */
    public function store(Request $request)
    {
        $request->validate([
            'server_auth_code' => 'required|string',
        ]);

        $code = $request->input('server_auth_code');

        // For mobile Native Google Sign-In, the redirect URI during token exchange must match the one sent, 
        // which is usually an empty string for Android/iOS server_auth_code exchange.
        // We set it to empty string before calling authenticateAndSave.
        $this->calendarService->setRedirectUriForApi();

        $success = $this->calendarService->authenticateAndSave($code, Auth::user());

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Google Calendar connected successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to authenticate with Google Calendar using the provided code.',
        ], 400);
    }
}
