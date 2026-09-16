<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleCalendarController extends Controller
{
    protected GoogleCalendarService $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Redirect user to Google OAuth consent screen
     */
    public function connect()
    {
        $url = $this->calendarService->getAuthUrl();
        return redirect()->away($url);
    }

    /**
     * Handle the OAuth callback from Google
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('profile.edit')->with('error', 'Google Calendar connection was denied or failed.');
        }

        $code = $request->input('code');

        if (!$code) {
            return redirect()->route('profile.edit')->with('error', 'Invalid Google callback request.');
        }

        $success = $this->calendarService->authenticateAndSave($code, Auth::user());

        if ($success) {
            return redirect()->route('profile.edit')->with('success', 'Google Calendar connected successfully! Site Visits will now sync to your calendar.');
        }

        return redirect()->route('profile.edit')->with('error', 'Failed to authenticate with Google Calendar.');
    }
}
