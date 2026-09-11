<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeadSource;
use App\Services\LeadSources\LeadSourceManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadSourceWebhookController extends Controller
{
    /**
     * Webhook Verification Handshake (e.g. Meta / Facebook Graph API hub.challenge)
     */
    public function verify(Request $request, string $type, string $token)
    {
        $hubChallenge = $request->query('hub_challenge')
            ?? $request->query('hub.challenge')
            ?? $_GET['hub_challenge']
            ?? $_GET['hub_challenge']
            ?? null;

        if (!$hubChallenge) {
            // Check raw query string if PHP sanitized dot parameter
            $queryString = $request->getQueryString() ?? '';
            if (preg_match('/hub[._]challenge=([^&]+)/', $queryString, $matches)) {
                $hubChallenge = urldecode($matches[1]);
            }
        }

        Log::info("[META VERIFICATION] Type: {$type}, Token: {$token}, Challenge: {$hubChallenge}", $request->all());

        // Return hub.challenge directly as plain text HTTP 200 for Meta Handshake
        if (!empty($hubChallenge)) {
            return response($hubChallenge, 200)->header('Content-Type', 'text/plain');
        }

        $source = LeadSource::where('webhook_token', $token)->first();

        return response()->json([
            'status' => 'active',
            'source' => $source?->name ?? 'REOS Webhook Listener',
            'type' => $type,
        ], 200);
    }


    /**
     * Handle incoming Lead Webhook Payload (POST)
     */
    public function handle(Request $request, string $type, string $token, LeadSourceManager $manager)
    {
        Log::info("[WEBHOOK RECEIVED] Type: {$type}, Token: {$token}", $request->all());

        $source = LeadSource::where('webhook_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$source) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive webhook token.',
            ], 404);
        }

        $result = $manager->processIncomingLead($source, $request->all());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Lead received and processed successfully.',
                'lead_code' => $result['lead']->lead_code,
                'is_duplicate' => $result['is_duplicate'] ?? false,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to ingest lead payload.',
        ], 400);
    }
}
