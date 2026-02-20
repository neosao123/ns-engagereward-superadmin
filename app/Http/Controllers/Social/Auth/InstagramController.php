<?php

namespace App\Http\Controllers\Social\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia\InstagramSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InstagramController extends Controller
{
    private function getInstagramConfig()
    {
        $setting = InstagramSetting::first();
        if ($setting) {
            return [
                'app_id' => decryptData($setting->app_id),
                'app_secret' => decryptData($setting->app_secret)
            ];
        }
        return null;
    }

    public function redirect(Request $request)
    {
        $config = $this->getInstagramConfig();
        if (!$config) {
             return response('Instagram API keys not configured.', 400);
        }

        $returnUrl = $request->query('return_url');

        // 1. Generate a unique session ID
        $sessionId = Str::uuid()->toString();

        // 2. Store return_url in cache (10 minutes)
        Cache::put('ig_auth_' . $sessionId, [
            'return_url' => $returnUrl
        ], 600);

        // 3. Build Instagram OAuth URL
        $params = http_build_query([
            'client_id'     => $config['app_id'],
            'redirect_uri'  => env('IG_REDIRECT_URI'),
            'response_type' => 'code',
            'scope'         => implode(',', [
                'instagram_business_basic',
                'instagram_business_content_publish',
                'instagram_business_manage_messages',
                'instagram_business_manage_comments',
            ]),
            'state' => $sessionId,
        ]);

        return redirect("https://www.instagram.com/oauth/authorize?{$params}");
    }

    public function callback(Request $request)
    {
        $config = $this->getInstagramConfig();
        if (!$config) {
            return response('Instagram API keys not configured.', 400);
        }

        $appId = $config['app_id'];
        $appSecret = $config['app_secret'];

        try {
            // 1. Get session ID from state
            $sessionId = $request->input('state');

            if (!$sessionId) {
                throw new \Exception('State parameter missing from Instagram response.');
            }

            
            $cachedData = Cache::pull('ig_auth_' . $sessionId);

            if (!$cachedData || !isset($cachedData['return_url'])) {
                throw new \Exception('Session expired or invalid state.');
            }

            $returnUrl = $cachedData['return_url'];

           
            if ($request->has('error')) {
                Log::warning('Instagram OAuth cancelled', [
                    'error'  => $request->input('error'),
                    'reason' => $request->input('error_reason'),
                ]);
                return redirect($returnUrl . '?error=auth_cancelled');
            }

           
            $code = $request->input('code');
            if (!$code) {
                throw new \Exception('No authorization code received from Instagram.');
            }

           
            $shortTokenResponse = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                'client_id'     => $appId,
                'client_secret' => $appSecret,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => env('IG_REDIRECT_URI'),
                'code'          => $code,
            ]);

            if ($shortTokenResponse->failed()) {
                throw new \Exception('Short-lived token exchange failed: ' . $shortTokenResponse->body());
            }

            $shortData = $shortTokenResponse->json();

            // Handle response structure
            if (isset($shortData['data'][0])) {
                $shortLivedToken = $shortData['data'][0]['access_token'];
                $igUserId        = $shortData['data'][0]['user_id'];
            } elseif (isset($shortData['access_token'])) {
                $shortLivedToken = $shortData['access_token'];
                $igUserId        = $shortData['user_id'];
            } else {
                throw new \Exception('Unexpected token response: ' . json_encode($shortData));
            }

            // 6. Short-lived → Long-lived Token (60 days)
            // GET https://graph.instagram.com/access_token
            $longTokenResponse = Http::get('https://graph.instagram.com/access_token', [
                'grant_type'    => 'ig_exchange_token',
                'client_secret' => $appSecret,
                'access_token'  => $shortLivedToken,
            ]);

            if ($longTokenResponse->failed()) {
                throw new \Exception('Long-lived token exchange failed: ' . $longTokenResponse->body());
            }

            $longData       = $longTokenResponse->json();
            $longLivedToken = $longData['access_token'];
            $expiresIn      = $longData['expires_in']; // ~5183944 seconds = 60 days

            // 7. Prepare payload
            $payload = json_encode([
                'ig_user_id'   => $igUserId,
                'access_token' => $longLivedToken,
                'expires_in'   => $expiresIn,
            ]);

            // 8. Encrypt payload (same as Facebook controller)
            $key         = env('FB_PROXY_SECRET');
            $iv          = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted   = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);
            $tokenParams = base64_encode($iv . '::' . $encrypted);

            // 9. Redirect back to admin dashboard
            return redirect($returnUrl . '?auth_token=' . urlencode($tokenParams));

        } catch (\Exception $e) {
            Log::error('Instagram callback error: ' . $e->getMessage());

            if (isset($returnUrl)) {
                return redirect($returnUrl . '?error=auth_failed');
            }

            return response('Authentication failed. Please try again from your dashboard.', 400);
        }
    }

    public function deauthorize(Request $request)
    {
        Log::info('SOCIAL INSTAGRAM DEAUTHORIZE', ['request' => $request->all()]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Deauthorized successfully',
        ], 200);
    }

    public function data_delete(Request $request)
    {
        Log::info('SOCIAL INSTAGRAM DATA DELETE', ['request' => $request->all()]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data deleted successfully',
        ], 200);
    }
}