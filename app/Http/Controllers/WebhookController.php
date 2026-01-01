<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\LogHelper;
use App\Models\Company;
use App\Models\CompanyDatabase;
use App\Models\SubscriptionPurchase;
use App\Models\PaymentSetting;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\HandlesAdminApiRequests;
use App\Traits\HandlesApiResponses;
use Stripe\WebhookEndpoint;
use App\Services\PackageDbService;

class WebhookController extends Controller
{

    protected $stripePublishableKey;
    protected $stripeSecretKey;
    protected $stripeWebhookSecret;
    protected $packageDbService;

    public function __construct(PackageDbService $packageDbService)
    {
        $this->packageDbService = $packageDbService;
        $this->loadStripeCredentials();
    }

    private function loadStripeCredentials()
    {
        try {
            $paymentSetting = PaymentSetting::where('is_active', 1)->first();

            if ($paymentSetting) {
                $paymentMode = $paymentSetting->payment_mode;

                if ($paymentMode == 1) {
                    $this->stripePublishableKey = $paymentSetting->live_client_id ?? '';
                    $this->stripeSecretKey = $paymentSetting->live_secret_key ?? '';
                    $this->stripeWebhookSecret = $paymentSetting->webhook_secret_live_key ?? '';
                } else {
                    $this->stripePublishableKey = $paymentSetting->test_client_id ?? '';
                    $this->stripeSecretKey = $paymentSetting->test_secret_key ?? '';
                    $this->stripeWebhookSecret = $paymentSetting->webhook_secret_key ?? '';
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to load Stripe credentials: ' . $e->getMessage());
        }
    }

    public function stripewebhook(Request $r)
    {

        Log::info('=== Webhook Request Received ===');
        Log::info('Headers: ' . json_encode($r->headers->all()));
        Log::info('Method: ' . $r->method());
        Log::info('URL: ' . $r->fullUrl());

        try {

            $payload = $r->getContent();
            $sig_header = $r->header('Stripe-Signature');


            Log::info('Payload length: ' . strlen($payload));
            Log::info('Signature header: ' . ($sig_header ?? 'NOT FOUND'));
            Log::info('Webhook secret configured: ' . (!empty($this->stripeWebhookSecret) ? 'YES' : 'NO'));
            Log::info('Webhook secret prefix: ' . substr($this->stripeWebhookSecret ?? '', 0, 10));


            if (empty($this->stripeWebhookSecret)) {
                Log::error('❌ Webhook secret is empty!');
                return response()->json(['error' => 'Webhook not configured'], 500);
            }


            try {
                \Stripe\Stripe::setApiKey($this->stripeSecretKey);

                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $sig_header,
                    $this->stripeWebhookSecret
                );

                Log::info('✅ Signature verified successfully!');
            } catch (\UnexpectedValueException $e) {
                Log::error('❌ Invalid payload: ' . $e->getMessage());
                return response()->json(['error' => 'Invalid payload'], 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                Log::error('❌ Invalid signature: ' . $e->getMessage());
                Log::error('Expected webhook secret starting with: whsec_');
                Log::error('Your webhook secret starts with: ' . substr($this->stripeWebhookSecret ?? '', 0, 7));
                return response()->json(['error' => 'Invalid signature'], 400);
            }


            Log::info("✅ Payment webhook event received", [
                'type' => $event->type,
                'id' => $event->id,
                'livemode' => $event->livemode
            ]);
            Log::info("Payment web hook full event: " . json_encode($event));

            // Rest of your switch case code...
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    Log::info('Processing payment success for: ' . $paymentIntent->id);
                    $this->handlePaymentSuccess($paymentIntent);
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    Log::info('Processing payment failure for: ' . $paymentIntent->id);
                    $this->handlePaymentFailure($paymentIntent);
                    break;

                case 'payment_intent.canceled':
                    $paymentIntent = $event->data->object;
                    Log::info('Processing payment cancellation for: ' . $paymentIntent->id);
                    $this->handlePaymentCancellation($paymentIntent);
                    break;

                default:
                    Log::info('Unhandled webhook event type: ' . $event->type);
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $ex) {
            Log::error("❌ Webhook error: " . $ex->getMessage());
            Log::error("Stack trace: " . $ex->getTraceAsString());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }


    private function handlePaymentSuccess($paymentIntent)
    {
        DB::beginTransaction();
        try {
            $purchase = SubscriptionPurchase::where('payment_id', $paymentIntent->id)->first();

            if ($purchase && $purchase->payment_status !== 'paid') {
                $purchase->payment_status = 'paid';
                $purchase->is_active = 1;
                $purchase->status = 'inactive';
                $purchase->webhook_response = json_encode($paymentIntent);
                $purchase->save();

                // Update in company database
                if ($purchase->company_id) {
                    $company = CompanyDatabase::find($purchase->company_id);
                    if ($company) {
                        $this->packageDbService->updatePackagePaymentStatus(
                            $company,
                            $paymentIntent->id,
                            'paid',
                            1, // is_active
                            'inactive', // status
                            json_encode($paymentIntent)
                        );
                    }
                }

                Log::info('Payment succeeded for purchase ID: ' . $purchase->id);
                DB::commit();
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error handling payment success: ' . $exception->getMessage());
        }
    }

    private function handlePaymentFailure($paymentIntent)
    {
        DB::beginTransaction();
        try {
            $purchase = SubscriptionPurchase::where('payment_id', $paymentIntent->id)->first();

            if ($purchase) {
                $purchase->payment_status = 'failed';
                $purchase->is_active = 0;
                $purchase->status = 'inactive';
                $purchase->webhook_response = json_encode($paymentIntent);
                $purchase->save();

                // Update in company database
                if ($purchase->company_id) {
                    $company = CompanyDatabase::find($purchase->company_id);
                    if ($company) {
                        $this->packageDbService->updatePackagePaymentStatus(
                            $company,
                            $paymentIntent->id,
                            'failed',
                            0, // is_active
                            'inactive', // status
                            json_encode($paymentIntent)
                        );
                    }
                }

                Log::error('Payment failed for purchase ID: ' . $purchase->id);
                DB::commit();
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error handling payment failure: ' . $exception->getMessage());
        }
    }

    private function handlePaymentCancellation($paymentIntent)
    {
        DB::beginTransaction();
        try {
            $purchase = SubscriptionPurchase::where('payment_id', $paymentIntent->id)->first();

            if ($purchase) {
                $purchase->payment_status = 'cancel';
                $purchase->is_active = 0;
                $purchase->status = 'inactive';
                $purchase->webhook_response = json_encode($paymentIntent);
                $purchase->save();

                // Update in company database
                if ($purchase->company_id) {
                    $company = CompanyDatabase::find($purchase->company_id);
                    if ($company) {
                        $this->packageDbService->updatePackagePaymentStatus(
                            $company,
                            $paymentIntent->id,
                            'cancel',
                            0, // is_active
                            'inactive', // status
                            json_encode($paymentIntent)
                        );
                    }
                }

                Log::info('Payment cancelled for purchase ID: ' . $purchase->id);
                DB::commit();
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error handling payment cancellation: ' . $exception->getMessage());
        }
    }
}
