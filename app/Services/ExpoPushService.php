<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    /**
     * Expo Push API endpoint
     */
    private const EXPO_API_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send push notification to a single Expo push token
     *
     * @param  string  $token Expo push token
     * @param  string  $title Notification title
     * @param  string  $body Notification body
     * @param  array<string, mixed>  $data Additional data to send with the notification
     * @param  array<string, mixed>  $options Additional notification options (sound, badge, etc.)
     * @return array<string, mixed>|null Response from Expo API or null on failure
     */
    public function sendPush(
        string $token,
        string $title,
        string $body,
        array $data = [],
        array $options = []
    ): ?array {
        if (empty($token)) {
            Log::warning('ExpoPushService: Attempted to send push notification with empty token');

            return null;
        }

        $payload = [
            'to' => $token,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            ...$options,
        ];

        return $this->sendBatch([$payload]);
    }

    /**
     * Send push notifications to multiple Expo push tokens
     *
     * @param  array<int, array<string, mixed>>  $messages Array of notification messages
     * @return array<string, mixed>|null Response from Expo API or null on failure
     */
    public function sendBatch(array $messages): ?array
    {
        if (empty($messages)) {
            Log::warning('ExpoPushService: Attempted to send empty batch of notifications');

            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Content-Type' => 'application/json',
                ])
                ->post(self::EXPO_API_URL, $messages);

            if ($response->successful()) {
                $responseData = $response->json();

                // Log any errors from Expo API
                if (isset($responseData['data'])) {
                    foreach ($responseData['data'] as $index => $result) {
                        if (isset($result['status']) && $result['status'] === 'error') {
                            Log::warning('ExpoPushService: Error in push notification', [
                                'message_index' => $index,
                                'error' => $result['message'] ?? 'Unknown error',
                                'details' => $result['details'] ?? null,
                            ]);
                        }
                    }
                }

                return $responseData;
            }

            Log::error('ExpoPushService: Failed to send push notifications', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('ExpoPushService: Exception while sending push notifications', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Validate if a token is a valid Expo push token format
     *
     * @param  string  $token Token to validate
     * @return bool True if token format is valid
     */
    public function isValidToken(string $token): bool
    {
        // Expo push tokens start with "ExponentPushToken[" or "ExpoPushToken["
        // and end with "]"
        return preg_match('/^(ExponentPushToken|ExpoPushToken)\[.+\]$/', $token) === 1;
    }
}

