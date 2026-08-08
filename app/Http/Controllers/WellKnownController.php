<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Serves the two "domain ownership" files the mobile app needs so
 * WebAuthn platform authenticators (Face ID/Touch ID/fingerprint) work
 * inside the Capacitor WebView the same way they do in a normal browser --
 * without these, iOS/Android have no way to know the native app and this
 * website are the same trusted party, and silently refuse to hand
 * passkeys created in the app to a browser (or vice versa).
 *
 * Both files require values that only exist once real developer
 * accounts/signing keys exist (see mobile/README.md) -- until then they're
 * populated from .env placeholders and are harmless if left unfilled: they
 * just mean passkeys work in the browser but not (yet) inside the app.
 */
class WellKnownController extends Controller
{
    public function appleAppSiteAssociation(): JsonResponse
    {
        $teamId = config('services.apple.team_id');
        $bundleId = 'com.bluepeakfintech.app';

        return response()->json([
            'webcredentials' => [
                'apps' => $teamId ? ["{$teamId}.{$bundleId}"] : [],
            ],
        ]);
    }

    public function assetLinks(): JsonResponse
    {
        $fingerprints = array_values(array_filter(explode(',', (string) config('services.android.sha256_fingerprints'))));

        return response()->json([
            [
                'relation' => ['delegate_permission/common.get_login_creds'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => 'com.bluepeakfintech.app',
                    'sha256_cert_fingerprints' => $fingerprints,
                ],
            ],
        ]);
    }
}
