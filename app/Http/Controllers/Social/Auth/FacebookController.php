<?php

namespace App\Http\Controllers\Social\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;


class FacebookController extends Controller
{
    public function redirect(Request $request)
    {
        $returnUrl = $request->query('return_url');

        // 1. Generate a unique, short ID for this login attempt
        $sessionId = Str::uuid()->toString();

        // 2. Store the real data in your Server Cache for 10 minutes
        // Key: 'fb_auth_{uuid}', Value: 'https://site.com/abc/callback'
        Cache::put('fb_auth_' . $sessionId, [
            'return_url' => $returnUrl
        ], 600); // 600 seconds = 10 minutes

        // 3. Send ONLY the UUID to Facebook as the state
        return Socialite::driver('facebook')
            ->scopes([
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts'
            ])
            ->with(['state' => $sessionId])
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            // 1. Retrieve the UUID from the state parameter
            $sessionId = $request->input('state');

            if (!$sessionId) {
                // Fallback: If state is missing entirely, try checking previous URL or abort
                throw new \Exception('State parameter missing from Facebook response.');
            }

            // 2. Look up the original data from your Cache
            $cachedData = Cache::pull('fb_auth_' . $sessionId); // 'pull' retrieves and deletes it

            if (!$cachedData || !isset($cachedData['return_url'])) {
                throw new \Exception('Session expired or invalid state.');
            }

            $returnUrl = $cachedData['return_url'];

            // 3. Get User from Facebook
            $fbUser = Socialite::driver('facebook')->stateless()->user();

            // 4. Prepare Payload for Tenant (Same as before)
            $payload = json_encode([
                'fb_user_id' => $fbUser->id,
                'fb_email' => $fbUser->email,
                'user_access_token' => $fbUser->token,
                'expires_in' => $fbUser->expiresIn,
            ]);

            // 5. Encrypt (Same as before)
            $key = env('FB_PROXY_SECRET');
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);
            $tokenParams = base64_encode($iv . '::' . $encrypted);

            // 6. Redirect to the retrieved URL
            return redirect($returnUrl . '?auth_token=' . urlencode($tokenParams));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // Log the error for debugging: \Log::error($e->getMessage());
            // If we have the returnUrl (from cache), send them back with error
            if (isset($returnUrl)) {
                return redirect($returnUrl . '?error=auth_failed');
            }
            // If we don't know where to send them, show a generic error page
            return response('Authentication failed. Please try again from your dashboard.', 400);
        }
    }

    public function deauthorize(Request $request)
    {
        Log::info("SOCIAL FACEBOOK DEAUTHORIZE", ["request" => $request->all()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Deauthorized successfully',
        ], 200);
    }

    public function data_delete(Request $request)
    {
        Log::info("SOCIAL FACEBOOK DATA DELETE", ["request" => $request->all()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data deleted successfully',
        ], 200);
    }
} 
