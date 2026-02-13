<?php

namespace App\Http\Controllers\SocialMedia;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia\FacebookSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;

class FacebookSettingController extends Controller
{
    /**
     * Display the Facebook configuration page.
     */
    public function index()
    {
        try {
            $config = FacebookSetting::first();
            $setting = Setting::first();

            LogHelper::logSuccess(
                'success',
                'Facebook configuration page loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return view('main.social-media-setting.facebook.index', compact('config', 'setting'));
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'An error occurred while loading the Facebook configuration page',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while opening the Facebook configuration page');
        }
    }

    /**
     * Update Facebook App Keys (App ID and App Secret).
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

            $config = FacebookSetting::first() ?? new FacebookSetting();
            $config->app_id = $request->app_id;
            $config->app_secret = $request->app_secret;
            $config->save();

            LogHelper::logSuccess(
                'success',
                'Facebook App Keys updated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('status', 'Facebook App Keys updated successfully!');
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'An error occurred while updating Facebook App Keys',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while updating Facebook App Keys');
        }
    }
}
