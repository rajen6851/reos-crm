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
        $source = LeadSource::where('webhook_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$source) {
            return response()->json(['error' => 'Invalid or inactive webhook token'], 404);
        }

        // Meta Challenge Response
        $hubMode = $request->get('hub_mode', $request->get('hub.mode'));
        $hubToken = $request->get('hub_verify_token', $request->get('hub.verify_token'));
        $hubChallenge = $request->get('hub_challenge', $request->get('hub.challenge'));

        if ($hubMode && $hubChallenge) {
            $expectedToken = $source->credentials['verify_token'] ?? $source->webhook_token;
            if ($hubToken === $expectedToken || $hubToken === $source->webhook_token) {
                return response($hubChallenge, 200)->header('Content-Type', 'text/plain');
            }
        }

        return response()->json([
            'status' => 'active',
            'source' => $source->name,
            'type' => $source->type,
            'company_id' => $source->company_id,
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
