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
    // Exact URL that must be in Meta Dashboard -> Facebook Login -> Valid OAuth Redirect URIs
    private $redirectUrl = 'https://root.engagereward.com/social/auth/facebook/callback';

    public function redirect(Request $request)
    {
        $returnUrl = $request->query('return_url');
        if (!$returnUrl) {
            return response('Return URL is required.', 400);
        }

        $sessionId = Str::uuid()->toString();

        Cache::put('fb_auth_' . $sessionId, [
            'return_url' => $returnUrl
        ], 600);

        return Socialite::driver('facebook')
            ->stateless()
            ->redirectUrl($this->redirectUrl)
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
            $sessionId = $request->input('state');
            if (!$sessionId) {
                Log::error('Facebook Callback: Missing state parameter.');
                throw new \Exception('Missing state parameter.');
            }

            $cachedData = Cache::pull('fb_auth_' . $sessionId);
            if (!$cachedData || !isset($cachedData['return_url'])) {
                throw new \Exception('Session expired or invalid state.');
            }

            $returnUrl = $cachedData['return_url'];

            $fbUser = Socialite::driver('facebook')
                ->stateless()
                ->redirectUrl($this->redirectUrl)
                ->user();

            $payload = json_encode([
                'fb_user_id'        => $fbUser->id,
                'fb_email'          => $fbUser->email,
                'user_access_token' => $fbUser->token,
                'expires_in'        => $fbUser->expiresIn,
            ]);

            $key = env('FB_PROXY_SECRET');
            $iv  = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);
            $tokenParams = base64_encode($iv . '::' . $encrypted);

            return redirect($returnUrl . '?auth_token=' . urlencode($tokenParams));

        } catch (\Exception $e) {
            Log::error('Facebook Proxy Error: ' . $e->getMessage());
            if (isset($returnUrl)) {
                return redirect($returnUrl . '?error=auth_failed');
            }
            return response('Authentication failed: ' . $e->getMessage(), 400);
        }
    }

    public function deauthorize(Request $request)
    {
        Log::info("SOCIAL FACEBOOK DEAUTHORIZE", ["request" => $request->all()]);
        return response()->json(['status' => 'success']);
    }

    public function data_delete(Request $request)
    {
        Log::info("SOCIAL FACEBOOK DATA DELETE", ["request" => $request->all()]);
        return response()->json(['status' => 'success']);
    }
}
