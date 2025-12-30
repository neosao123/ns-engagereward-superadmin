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
use App\Models\Subscription;
use Carbon\Carbon;
use App\Models\SubscriptionPurchase;
use App\Traits\ApiSecurityTrait;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    use ApiSecurityTrait;
    /**
     * Get subscription package list
     */
    public function list(Request $request)
    {
        try {
            // Fetch only active subscriptions
            $today = Carbon::today()->toDateString();
            $subscriptions = Subscription::query()
                ->where('is_active', 1)
                ->whereDate('from_date', '<=', $today)
                ->whereDate('to_date', '>=', $today)
                ->select([
                    'id',
                    'subscription_title',
                    'subscription_months',
                    'subscription_per_month_price',
                    'subscription_total_price',
                    'discount_type',
                    'discount_value',
                    'from_date',
                    'to_date',
                    'currency_code'
                ])
                ->orderBy('subscription_months', 'asc')
                ->orderBy('subscription_total_price', 'asc')
                ->get();

            return response()->json([
                'status' => 200,
                'message' => 'Subscription list fetched successfully',
                'data' => $subscriptions
            ], 200);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'Error during subscription package',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__
            );
            return response()->json([
                'status' => 400,
                'message' => 'Something went wrong: ' . $ex->getMessage()
            ], 400);
        }
    }


    /**
     * Store subscription purchase API
     *
     * This API validates the request and stores
     * subscription purchase details in subscription_purchases table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscription_purchase(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->validateApiRequest($request);
            if ($data instanceof JsonResponse) {
                return $data;
            }

            $purchase = new SubscriptionPurchase();
            $purchase->subscription_id              = $data['subscription_id'] ?? null;
            $purchase->company_id                   = $data['company_id'] ?? null;
            $purchase->subscription_purchase_id      = $data['subscription_purchase_id'] ?? null;
            $purchase->subscription_title           = $data['subscription_title'] ?? null;
            $purchase->subscription_months          = $data['subscription_months'] ?? null;
            $purchase->subscription_per_month_price = $data['subscription_per_month_price'] ?? null;
            $purchase->subscription_total_price     = $data['subscription_total_price'] ?? null;
            $purchase->is_active                    = $data['is_active'] ?? true;
            $purchase->status                       = $data['status'] ?? "active";
            $purchase->from_date                    = $data['from_date'] ?? null;
            $purchase->to_date                      = $data['to_date'] ?? null;
            $purchase->payment_status               = $data['payment_status'] ?? null;
            $purchase->payment_order_id             = $data['payment_order_id'] ?? null;
            $purchase->payment_response             = $data['payment_response'] ?? null;
            $purchase->payment_id                   = $data['payment_id'] ?? null;
            $purchase->payment_mode                 = $data['payment_mode'] ?? null;
            $purchase->webhook_response             = $data['webhook_response'] ?? null;
            $purchase->discount_type                = $data['discount_type'] ?? null;
            $purchase->discount_value               = $data['discount_value'] ?? null;
            $purchase->currency_code                = $data['currency_code'] ?? null;
            $purchase->save();

            DB::commit();

            LogHelper::logSuccess('success', 'API => Subscription purchase stored successfully.',  __FUNCTION__, basename(__FILE__), __LINE__, "");

            return response()->json([
                'message' => "Subscription purchase stored successfully",
                'status'  => Response::HTTP_OK,
                'data'    => $purchase
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            LogHelper::logError(
                'exception',
                'Failed to store subscription purchase.',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ''
            );

            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.server_error'),
                'error'   => config('app.debug') ? $exception->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //update package purchase status by Admin
    public function update_purchase_package(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->validateApiRequest($request);
            if ($data instanceof JsonResponse) {
                return $data;
            }

            SubscriptionPurchase::where('company_id', $data['company_id'])
                ->where('subscription_purchase_id', $data['subscription_purchase_id'])
                ->update(['is_active' => $data['is_active'], 'status' => $data['status']]);

            DB::commit();

            LogHelper::logSuccess('success', 'API => ' . __('api.subscription_package_update'),  __FUNCTION__, basename(__FILE__), __LINE__, '');
            return response()->json([
                'message' => __('api.subscription_package_update'),
                'status' => Response::HTTP_OK,
            ]);
        } catch (\Exception $exception) {
            // Rollback if something went wrong
            DB::rollBack();

            LogHelper::logError(
                'exception',
                __('api.subscription_package_error'),
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ''
            );
            return $this->handleGeneralException($exception);
        }
    }


    //update package purchase status by Admin for order cancel or failed or success
    public function update_paypal_order_status(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->validateApiRequest($request);
            if ($data instanceof JsonResponse) {
                return $data;
            }

            SubscriptionPurchase::where('payment_id', $data['payment_id'])
                ->update(['webhook_response' => $data['webhook_response'], 'payment_status' => $data['payment_status'], 'is_active' => $data['is_active']]);

            DB::commit();

            LogHelper::logSuccess('success', 'API => ' . __('api.subscription_package_update'),  __FUNCTION__, basename(__FILE__), __LINE__, '');
            return response()->json([
                'message' => __('api.subscription_package_update'),
                'status' => Response::HTTP_OK,
            ]);
        } catch (\Exception $exception) {
            // Rollback if something went wrong
            DB::rollBack();

            LogHelper::logError(
                'exception',
                __('api.subscription_package_error'),
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ''
            );
            return $this->handleGeneralException($exception);
        }
    }
}
