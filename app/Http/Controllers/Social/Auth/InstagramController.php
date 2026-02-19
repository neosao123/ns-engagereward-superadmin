<?php 

namespace App\Http\Controllers\Social\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class InstagramController extends Controller
{
    public function redirect(Request $request)
    {
        $returnUrl = $request->query('return_url');

        $sessionId = Str::uuid()->toString();

        Cache::put('ig_auth_' . $sessionId, [
            'return_url' => $returnUrl
        ], 600);

        return Socialite::driver('facebook')
            ->scopes([
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts',
                'instagram_basic',
                'instagram_manage_insights',
                'instagram_content_publish'
            ])
            ->with(['state' => $sessionId])
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $sessionId = $request->input('state');

            $cachedData = Cache::pull('ig_auth_' . $sessionId);

            if (!$cachedData) {
                throw new \Exception('Invalid state.');
            }

            $returnUrl = $cachedData['return_url'];

            $fbUser = Socialite::driver('facebook')->stateless()->user();

            $payload = json_encode([
                'fb_user_id' => $fbUser->id,
                'user_access_token' => $fbUser->token,
                'expires_in' => $fbUser->expiresIn,
            ]);  

            $key = env('FB_PROXY_SECRET');
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);

            $tokenParams = base64_encode($iv . '::' . $encrypted);

            return redirect($returnUrl . '?auth_token=' . urlencode($tokenParams));

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response('Instagram Authentication Failed', 400);
        }
    }
}
