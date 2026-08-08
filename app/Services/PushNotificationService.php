<?php

namespace App\Services;

use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around OneSignal's REST API ("Create notification" endpoint).
 * Targets a single user by the external_id set on their device in the
 * mobile app (OneSignal.login(user_id) after the customer authenticates) --
 * see the Capacitor app's auth bridge.
 */
class PushNotificationService
{
    /**
     * @return array{success: bool, error: ?string}
     */
    public static function sendToUser(int $userId, string $title, string $body): array
    {
        $settings = NotificationSetting::current();

        if (! $settings->pushReady()) {
            return ['success' => false, 'error' => 'OneSignal is not configured or enabled.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.$settings->onesignal_api_key,
                'Content-Type' => 'application/json; charset=utf-8',
            ])->post('https://onesignal.com/api/v1/notifications', [
                'app_id' => $settings->onesignal_app_id,
                'include_aliases' => ['external_id' => [(string) $userId]],
                'target_channel' => 'push',
                'headings' => ['en' => $title],
                'contents' => ['en' => $body],
            ]);

            if ($response->successful() && ! ($response->json('errors'))) {
                return ['success' => true, 'error' => null];
            }

            return ['success' => false, 'error' => $response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Broadcasts to every device that has ever logged in with an
     * external_id (i.e. every app user), used by the admin's manual
     * "send to all customers" composer.
     */
    public static function sendToAll(string $title, string $body): array
    {
        $settings = NotificationSetting::current();

        if (! $settings->pushReady()) {
            return ['success' => false, 'error' => 'OneSignal is not configured or enabled.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.$settings->onesignal_api_key,
                'Content-Type' => 'application/json; charset=utf-8',
            ])->post('https://onesignal.com/api/v1/notifications', [
                'app_id' => $settings->onesignal_app_id,
                'included_segments' => ['Subscribed Users'],
                'headings' => ['en' => $title],
                'contents' => ['en' => $body],
            ]);

            if ($response->successful() && ! ($response->json('errors'))) {
                return ['success' => true, 'error' => null];
            }

            return ['success' => false, 'error' => $response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
