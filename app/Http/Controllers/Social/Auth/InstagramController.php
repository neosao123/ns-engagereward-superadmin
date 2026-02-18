<?php

namespace App\Http\Controllers\Social\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;

class InstagramController extends Controller
{
    // Ensure this URL is added to "Valid OAuth Redirect URIs" in your Meta App Dashboard
    private $redirectUrl = 'https://root.engagereward.com/social/auth/instagram/callback';

    /**
     * Redirect to Facebook Login with Instagram Scopes
     */
    public function redirect(Request $request)
    {
        $returnUrl = $request->query('return_url');
        if (!$returnUrl) {
            return response('Return URL is required.', 400);
        }

        $sessionId = Str::uuid()->toString();

        Cache::put('ig_auth_' . $sessionId, [
            'return_url' => $returnUrl
        ], 600);

        // Using stateless() on redirect ensures Socialite doesn't try to manage its own state in the session
        return Socialite::driver('facebook')
            ->stateless()
            ->redirectUrl($this->redirectUrl)
            ->scopes([
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_metadata', 
                'instagram_basic',
                'instagram_manage_insights',
                'instagram_manage_comments',
                'instagram_content_publish',
            ])
            ->with(['state' => $sessionId])
            ->redirect();
    }

    /**
     * Handle Callback from Meta
     */
    public function callback(Request $request)
    {
        try {
            $sessionId = $request->input('state');
            
            if (!$sessionId) {
                Log::error('Instagram Callback: Missing state parameter. Request query: ' . json_encode($request->query()));
                throw new \Exception('Missing state parameter.');
            }

            $cachedData = Cache::pull('ig_auth_' . $sessionId);
            if (!$cachedData || !isset($cachedData['return_url'])) {
                throw new \Exception('Session expired or invalid state.');
            }

            $returnUrl = $cachedData['return_url'];

            // 1. Get User from Facebook Login
            $fbUser = Socialite::driver('facebook')
                ->redirectUrl($this->redirectUrl)
                ->stateless()
                ->user();

            $shortLivedToken = $fbUser->token;

            // 2. Exchange for a Long-Lived User Token (60 days)
            $longTokenResponse = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'grant_type'        => 'fb_exchange_token',
                'client_id'         => env('FACEBOOK_CLIENT_ID'),
                'client_secret'     => env('FACEBOOK_CLIENT_SECRET'),
                'fb_exchange_token' => $shortLivedToken,
            ]);

            if (!$longTokenResponse->successful()) {
                throw new \Exception('Failed to exchange for long-lived token.');
            }

            $longLivedToken = $longTokenResponse->json()['access_token'];

            // 3. Find Instagram Business Account linked to user's pages
            // We search through the user's pages to find the one connected to Instagram
            $pagesResponse = Http::get('https://graph.facebook.com/v19.0/me/accounts', [
                'access_token' => $longLivedToken
            ]);

            if (!$pagesResponse->successful()) {
                throw new \Exception('Failed to fetch Facebook pages.');
            }

            $pagesData = $pagesResponse->json()['data'] ?? [];
            $instagramAccountId = null;
            $linkedPageId = null;
            $linkedPageName = null;

            foreach ($pagesData as $page) {
                $pageId = $page['id'];
                $pageRes = Http::get("https://graph.facebook.com/v19.0/{$pageId}", [
                    'fields'       => 'instagram_business_account,name',
                    'access_token' => $longLivedToken
                ]);

                if ($pageRes->successful() && isset($pageRes->json()['instagram_business_account'])) {
                    $instagramAccountId = $pageRes->json()['instagram_business_account']['id'];
                    $linkedPageId = $pageId;
                    $linkedPageName = $pageRes->json()['name'] ?? $page['name'];
                    break;
                }
            }

            if (!$instagramAccountId) {
                throw new \Exception('No Instagram Business Account found linked to your Facebook Pages.');
            }

            // 4. Prepare Payload for Tenant
            $payload = json_encode([
                'fb_user_id'           => $fbUser->id,
                'fb_email'             => $fbUser->email,
                'page_id'              => $linkedPageId,
                'page_name'            => $linkedPageName,
                'instagram_account_id' => $instagramAccountId,
                'access_token'         => $longLivedToken, // Superadmin passes the power token back
                'expires_in'           => 5184000, // 60 days
            ]);

            // 5. Encrypt exactly like Facebook Proxy
            $key = env('FB_PROXY_SECRET');
            $iv  = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);
            $authToken = base64_encode($iv . '::' . $encrypted);

            return redirect($returnUrl . '?auth_token=' . urlencode($authToken));

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            if (isset($returnUrl)) {
                return redirect($returnUrl . '?error=' . urlencode($e->getMessage()));
            }
            return response('Authentication failed: ' . $e->getMessage(), 400);
        }
    }

    public function deauthorize(Request $request)
    {
        Log::info("SOCIAL INSTAGRAM DEAUTHORIZE", ["request" => $request->all()]);
        return response()->json(['status' => 'success']);
    }

    public function data_delete(Request $request)
    {
        Log::info("SOCIAL INSTAGRAM DATA DELETE", ["request" => $request->all()]);
        return response()->json(['status' => 'success']);
    }
}
