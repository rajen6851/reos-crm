<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send automated WhatsApp template message via Meta Graph API / WhatsApp Business API / Interakt / Wati.
     */
    public function sendWhatsAppMessage(string $phone, string $message, ?string $templateName = null): bool
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $apiUrl = config('services.whatsapp.api_url', env('WHATSAPP_API_URL'));
        $token = config('services.whatsapp.token', env('WHATSAPP_API_TOKEN'));
        $gatewayType = config('services.whatsapp.gateway_type', env('WHATSAPP_GATEWAY_TYPE', 'meta')); // 'meta' or 'local_qr'

        Log::info("WhatsApp API Dispatch [{$gatewayType}] to {$cleanPhone}: {$message}");

        if (!$apiUrl && $gatewayType !== 'click_to_chat') {
            Log::warning("WhatsApp API credentials missing in .env. Click-to-Chat trigger active: https://wa.me/{$cleanPhone}");
            return false;
        }

        try {
            if ($gatewayType === 'local_qr') {
                // Free Local WhatsApp Web QR Gateway (e.g. UltraMsg / Baileys / WPPConnect)
                $response = Http::post($apiUrl, [
                    'phone' => $cleanPhone,
                    'body' => $message,
                ]);
                return $response->successful();
            }

            // Meta Official WhatsApp Cloud API (Includes 1,000 Free Conversations per Month)
            $response = Http::withToken($token)->post($apiUrl, [
                'messaging_product' => 'whatsapp',
                'to' => $cleanPhone,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("WhatsApp API Dispatch Error to {$cleanPhone}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate instant Web WhatsApp Click-to-Chat link.
     */
    public function getClickToChatUrl(string $phone, string $customMessage = ''): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        return "https://wa.me/{$cleanPhone}?text=" . urlencode($customMessage);
    }
}
