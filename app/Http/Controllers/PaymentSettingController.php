<?php

/**
 * --------------------------------------------------------------------------------
 * This controller manages payment setting operations

 * @author
 *----------------------------------------------------------------------------------
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentSetting;
use App\Helpers\LogHelper;

class PaymentSettingController extends Controller
{


     /**
     * Apply middleware permissions for access control.
     * Each route is restricted to specific admin permissions.
     */
    public function __construct()
    {

        $this->middleware('permission:PaymentSetting.Create,admin')->only(['create', 'store']);

    }

    /**
     * Display and edit payment setting (single form for both add/edit)
     */
    public function create()
    {
        try {
            $paymentSetting = PaymentSetting::where('is_delete', 0)->first();

            LogHelper::logSuccess(
                'success',
                'Payment settings fetched successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                null
            );

            return view('main.payment-settings.add', compact('paymentSetting'));

        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An exception occurred while fetching payment settings',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                null
            );

            return redirect()->back()->with('error', 'An error occurred while fetching payment settings.');
        }
    }

    /**
     * Store or Update payment setting
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'payment_mode' => ['required', 'in:0,1'],
                'test_secret_key' => ['required', 'string'],
                'test_client_id' => ['required', 'string'],
                'live_secret_key' => ['required', 'string'],
                'live_client_id' => ['required', 'string'],
                'webhook_secret_key' => ['nullable', 'string'],
                'webhook_secret_live_key' => ['nullable', 'string'],
                'payment_gateway' => ['required', 'string', 'max:50'],
            ];

            $messages = [
                'payment_mode.required' => 'Payment mode is required.',
                'payment_mode.in' => 'Invalid payment mode selected.',
                'test_secret_key.required' => 'Test secret key is required.',
                'test_client_id.required' => 'Test client ID is required.',
                'live_secret_key.required' => 'Live secret key is required.',
                'live_client_id.required' => 'Live client ID is required.',
                'payment_gateway.required' => 'Payment gateway is required.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                LogHelper::logError(
                    'validation_error',
                    'Validation failed while saving payment setting',
                    json_encode($validator->errors()->all()),
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    null
                );

                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 200);
            }

            // Check if record exists
            $paymentSetting = PaymentSetting::where('is_delete', 0)->first();

            if ($paymentSetting) {
                // Update existing record
                $paymentSetting->payment_mode = $request->payment_mode;
                $paymentSetting->test_secret_key = $request->test_secret_key;
                $paymentSetting->test_client_id = $request->test_client_id;
                $paymentSetting->live_secret_key = $request->live_secret_key;
                $paymentSetting->live_client_id = $request->live_client_id;
                $paymentSetting->webhook_secret_key = $request->webhook_secret_key;
                $paymentSetting->webhook_secret_live_key = $request->webhook_secret_live_key;
                $paymentSetting->payment_gateway = $request->payment_gateway;
                $paymentSetting->is_active = 1;
                $paymentSetting->save();

                $message = 'Payment setting updated successfully.';
            } else {
                // Create new record
                $paymentSetting = new PaymentSetting();
                $paymentSetting->payment_mode = $request->payment_mode;
                $paymentSetting->test_secret_key = $request->test_secret_key;
                $paymentSetting->test_client_id = $request->test_client_id;
                $paymentSetting->live_secret_key = $request->live_secret_key;
                $paymentSetting->live_client_id = $request->live_client_id;
                $paymentSetting->webhook_secret_key = $request->webhook_secret_key;
                $paymentSetting->webhook_secret_live_key = $request->webhook_secret_live_key;
                $paymentSetting->payment_gateway = $request->payment_gateway;
                $paymentSetting->is_active = 1;
                $paymentSetting->is_delete = 0;
                $paymentSetting->save();

                $message = 'Payment setting created successfully.';
            }

            DB::commit();

            LogHelper::logSuccess(
                'success',
                $message,
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $paymentSetting->id
            );

            return response()->json([
                'status' => 200,
                'message' => $message,
                'data' => $paymentSetting
            ], 200);

        } catch (\Exception $ex) {
            DB::rollBack();

            LogHelper::logError(
                'exception',
                'Failed to save payment setting.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                null
            );

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the payment setting.',
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
