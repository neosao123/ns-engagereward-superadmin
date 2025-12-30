<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\PaymentSetting;
use Carbon\Carbon;
use App\Traits\ApiSecurityTrait;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    use ApiSecurityTrait;

    /**
     * Get payment setting
     */
    public function paymentSetting(Request $request)
    {
        try {
            $paymentSetting = PaymentSetting::query()
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->select([
                    'id',
                    'payment_mode',
                    'test_secret_key',
                    'test_client_id',
                    'live_secret_key',
                    'live_client_id',
                    'webhook_secret_key',
                    'webhook_secret_live_key',
                    'payment_gateway'
                ])
                ->first();

            return response()->json([
                'status' => 200,
                'message' => 'Payment setting fetched successfully',
                'data' => $paymentSetting
            ], 200);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'Error during payment setting',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ''
            );

            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong: ' . $ex->getMessage()
            ], 400);
        }
    }
}
