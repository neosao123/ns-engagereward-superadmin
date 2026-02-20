<?php

namespace App\Http\Controllers\SocialMedia;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia\InstagramSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class InstagramSettingController extends Controller
{
    /**
     * Display the Instagram configuration page.
     */
    public function index()
    {
        try {
            $config = InstagramSetting::first();
            $setting = Setting::first();

            LogHelper::logSuccess(
                'success',
                'Instagram configuration page loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return view('main.social-media-setting.instagram.index', compact('config', 'setting'));
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'An error occurred while loading the Instagram configuration page',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while opening the Instagram configuration page');
        }
    }

    /**
     * Update Instagram App Keys (App ID and App Secret).
     */
    public function updateAppKeys(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'app_id' => 'required|string',
                'app_secret' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $config = InstagramSetting::first() ?? new InstagramSetting();
            $config->app_id = encryptData($request->app_id);
            $config->app_secret = encryptData($request->app_secret);
            $config->save();

            // Note: These might need to match what's used in InstagramController (Auth)
            Config::set('services.instagram.client_id', decryptData($config->app_id));
            Config::set('services.instagram.client_secret', decryptData($config->app_secret));

            LogHelper::logSuccess(
                'success',
                'Instagram App Keys updated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('status', 'Instagram App Keys updated successfully!');
        } catch (\Exception $exception) {
            $message = 'An error occurred while updating keys as ' . $exception->getMessage();
            LogHelper::logError(
                'exception',
                $message,
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', $message);
        }
    }

    public function confirmPassword(Request $request)
    {
        $request->validate([
            'password' => 'required'
        ]);

        $admin = Auth::guard('admin')->user();

        if (!Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password'
            ], 422);
        }

        // Store confirmation timestamp in session
        Session::put('instagram_access_confirmed_at', Carbon::now());

        return response()->json([
            'success' => true
        ]);
    }

    public function getKeys()
    {
        $confirmedAt = session('instagram_access_confirmed_at');

        if (!$confirmedAt || now()->diffInMinutes($confirmedAt) > 10) {
            return response()->json([
                'success' => false,
                'expired' => true,
                'message' => 'Password confirmation expired'
            ], 403);
        }

        $config = InstagramSetting::first();

        if (!$config) {
            return response()->json([
                'success' => true,
                'app_id' => 'Not Set',
                'app_secret' => 'Not Set'
            ]);
        }

        return response()->json([
            'success' => true,
            'app_id' => decryptData($config->app_id) ?? 'Decryption Failed',
            'app_secret' => decryptData($config->app_secret) ?? 'Decryption Failed'
        ]);
    }
}
