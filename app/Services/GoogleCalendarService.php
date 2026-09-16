<?php

namespace App\Services;

use App\Models\GoogleCalendarConnection;
use App\Models\SiteVisit;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleCalendarService
{
    protected Google_Client $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(route('google-calendar.callback'));
        $this->client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
        // Important: we need userinfo.email scope to fetch the email
        $this->client->addScope('https://www.googleapis.com/auth/userinfo.email');
        $this->client->setAccessType('offline'); // Required for refresh token
        $this->client->setPrompt('consent'); // Force consent to get refresh token
    }

    /**
     * Get the OAuth URL for the user to authenticate.
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Set the redirect URI to empty string for mobile native OAuth flows
     * where the exchange is done using server_auth_code.
     */
    public function setRedirectUriForApi(): void
    {
        $this->client->setRedirectUri('');
    }

    /**
     * Handle the OAuth callback and save the connection for the user.
     */
    public function authenticateAndSave(string $code, $user): bool
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                Log::error('Google Calendar OAuth Error: ' . $token['error']);
                return false;
            }

            // Get user info to save email
            $oauth2 = new \Google_Service_Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();

            GoogleCalendarConnection::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_id' => $user->company_id,
                    'google_email' => $userInfo->email,
                    'access_token' => $token['access_token'],
                    'refresh_token' => $token['refresh_token'] ?? null, // Important: only present on first auth or forced consent
                    'token_expires_at' => now()->addSeconds($token['expires_in'] ?? 3599),
                    'status' => 'active',
                ]
            );

            return true;
        } catch (Throwable $e) {
            Log::error('Google Calendar Auth Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Set the access token for the client, refreshing if necessary.
     */
    protected function setAccessToken(GoogleCalendarConnection $connection): bool
    {
        if ($connection->status !== 'active') {
            return false;
        }

        $this->client->setAccessToken([
            'access_token' => $connection->access_token,
            'expires_in' => $connection->token_expires_at->diffInSeconds(now()),
        ]);

        if ($this->client->isAccessTokenExpired()) {
            if (!$connection->refresh_token) {
                $connection->update(['status' => 'revoked']);
                return false;
            }

            try {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($connection->refresh_token);
                
                if (isset($newToken['error'])) {
                    $connection->update(['status' => 'revoked']);
                    return false;
                }

                $connection->update([
                    'access_token' => $newToken['access_token'],
                    'token_expires_at' => now()->addSeconds($newToken['expires_in'] ?? 3599),
                ]);
            } catch (Throwable $e) {
                Log::error('Failed to refresh Google Token for User ' . $connection->user_id . ': ' . $e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * Sync a SiteVisit event to Google Calendar.
     */
    public function syncSiteVisit(SiteVisit $siteVisit): bool
    {
        // Must have an assigned user and a scheduled time
        if (!$siteVisit->assigned_to_user_id || !$siteVisit->scheduled_at) {
            return false;
        }

        $connection = GoogleCalendarConnection::where('user_id', $siteVisit->assigned_to_user_id)
            ->where('status', 'active')
            ->first();

        if (!$connection) {
            return false;
        }

        if (!$this->setAccessToken($connection)) {
            return false;
        }

        $service = new Google_Service_Calendar($this->client);
        $calendarId = $connection->calendar_id ?: 'primary';

        $eventTitle = 'Site Visit: ' . ($siteVisit->lead->first_name ?? 'Client') . ' ' . ($siteVisit->lead->last_name ?? '');
        $description = "Lead: {$siteVisit->lead->first_name} {$siteVisit->lead->last_name}\n";
        $description .= "Phone: {$siteVisit->lead->phone}\n";
        $description .= "Project: " . ($siteVisit->project->name ?? 'Any') . "\n";
        $description .= "Outcome: {$siteVisit->outcome}\n";
        
        $startDateTime = $siteVisit->scheduled_at->toRfc3339String();
        // Assume 1 hour duration if not specified
        $endDateTime = $siteVisit->scheduled_at->addHour()->toRfc3339String();

        $event = new Google_Service_Calendar_Event([
            'summary' => $eventTitle,
            'description' => $description,
            'start' => new Google_Service_Calendar_EventDateTime([
                'dateTime' => $startDateTime,
                'timeZone' => config('app.timezone', 'UTC'),
            ]),
            'end' => new Google_Service_Calendar_EventDateTime([
                'dateTime' => $endDateTime,
                'timeZone' => config('app.timezone', 'UTC'),
            ]),
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 60],
                    ['method' => 'popup', 'minutes' => 30],
                ],
            ],
        ]);

        try {
            if ($siteVisit->google_event_id) {
                // Update existing event
                $updatedEvent = $service->events->update($calendarId, $siteVisit->google_event_id, $event);
                
                $siteVisit->update([
                    'google_sync_status' => 'synced',
                    'google_synced_at' => now(),
                ]);
            } else {
                // Create new event
                $createdEvent = $service->events->insert($calendarId, $event);
                
                $siteVisit->update([
                    'google_event_id' => $createdEvent->getId(),
                    'google_sync_status' => 'synced',
                    'google_synced_at' => now(),
                ]);
            }
            return true;
        } catch (Throwable $e) {
            Log::error('Google Calendar Sync Failed for SiteVisit ' . $siteVisit->id . ': ' . $e->getMessage());
            
            $siteVisit->update([
                'google_sync_status' => 'failed',
            ]);
            
            return false;
        }
    }
}
