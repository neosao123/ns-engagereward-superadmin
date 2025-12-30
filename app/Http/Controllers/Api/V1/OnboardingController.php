<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanyDatabase;
use App\Models\Company;
use App\Models\SiteSetupStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\CompanySocialMediaSetting;
use App\Models\SubscriptionPurchase;
use Illuminate\Support\Facades\Hash;
use Mail;
use App\Models\User;

class OnboardingController extends Controller
{

    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('ADMIN_API_URL'), '/') . '/instance-setup/';
    }
    public function trial()
    {

        $companies = Company::get();
        if ($companies) {
            foreach ($companies as $company) {
                SiteSetupStep::create([
                    'company_id' => $company->id,
                    'step_name' => 'step_one',
                    'status' => 'pending',
                    'order_no' => 1
                ]);

                SiteSetupStep::create([
                    'company_id' => $company->id,
                    'step_name' => 'step_two',
                    'status' => 'pending',
                    'order_no' => 2
                ]);

                SiteSetupStep::create([
                    'company_id' => $company->id,
                    'step_name' => 'step_three',
                    'status' => 'pending',
                    'order_no' => 3
                ]);

                SiteSetupStep::create([
                    'company_id' => $company->id,
                    'step_name' => 'step_four',
                    'status' => 'pending',
                    'order_no' => 4
                ]);
                SiteSetupStep::create([
                    'company_id' => $company->id,
                    'step_name' => 'step_five',
                    'status' => 'pending',
                    'order_no' => 5
                ]);
            }
        }
    }

    public function step_one(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ]);

            $companyId = $request->company_id;
            $company = Company::where('id', $companyId)->first();

            $response = Http::asMultipart()->post("$this->baseUrl/step1.php", [
                'company_id' => $company->company_code,
            ]);


            if ($response->failed()) {
                return response()->json(['error' => 'Failed to process your request. Please try again'], 422);
            }

            $jsonResponse = $response->json();
            //DB::beginTransaction();

            $siteSetup = SiteSetupStep::where('company_id', $company->id)
                ->where('step_name', 'step_one')
                ->first();

            if ($jsonResponse['code'] === 1) {
                $siteSetup->created_at = date('Y-m-d H:i:s');
                $siteSetup->completed_at = date('Y-m-d H:i:s');
                $siteSetup->status = 'complete';
                $siteSetup->save();
                //update patsanstha code
                $company->company_code = $jsonResponse['data'];
                $company->setup_status = 1;
                $company->save();
                //commit changes
                //DB::commit();
                //return response
                return response()->json(['step' => 'one', 'msg' => $jsonResponse['msg'], "company_id" => $jsonResponse['data']], 200);
            } else {
                $siteSetup->request_data = json_encode($request->all());
                $siteSetup->response_data = json_encode($jsonResponse);
                $siteSetup->save();
                //DB::commit();
                return response()->json(['msg' => $jsonResponse['msg'], 'res' => $jsonResponse], 300);
            }
        } catch (\Exception $e) {
            Log::debug("step 1 error", [$e]);
            //DB::rollBack();
            return response()->json(['msg' => 'Database update failed', 'ex' => $e->getMessage()], 500);
        }
    }

    public function step_two(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ]);

            $companyId = $request->company_id;
            $company = Company::where('id', $companyId)->first();
            $database = CompanyDatabase::where('company_id', $company->id)->first();

            $postData = [
                'company_id' => $company->company_code,
                'db_name' => $database && isset($database->db_name) ? $database->db_name : '#NA#',
                'db_user' => $database && isset($database->db_name) ? $database->db_username : '#NA#',
                'db_password' => $database && isset($database->db_name) ? $database->db_password : '#NA#',
            ];

            $response = Http::asMultipart()->post("$this->baseUrl/step2.php", $postData);


            if ($response->failed()) {
                return response()->json(['error' => "Failed to process your request at the moment..."], 422);
            }

            $jsonResponse = $response->json();

            //DB::beginTransaction();

            $siteSetup = SiteSetupStep::where('company_id', $company->id)
                ->where('step_name', 'step_two')
                ->first();

            if ($jsonResponse['code'] === 1) {
                $siteSetup->created_at = date('Y-m-d H:i:s');
                $siteSetup->completed_at = date('Y-m-d H:i:s');
                $siteSetup->status = 'complete';
                $siteSetup->save();

                $data = $jsonResponse['data'];
                //add or update database credentials
                CompanyDatabase::updateOrCreate(
                    [
                        'company_id' => $company->id,
                    ],
                    [
                        'company_id' => $company->id,
                        'db_name' => $data['db_name'],
                        //'db_username' => $data['db_user'],
                        //'db_password' => $data['db_password'],
                        'db_username' => env('INS_DB_USER'),
                        'db_password' => env('INS_DB_PASSWORD'),
                        'db_host' => 'localhost',
                        'db_port' => 3306,
                    ]
                );
                //commit changes
                //DB::commit();
                //return response
                return response()->json(['step' => 'two', 'msg' => $jsonResponse['msg']], 200);
            } else {
                $siteSetup->request_data = json_encode($request->all());
                $siteSetup->response_data = json_encode($jsonResponse);
                $siteSetup->save();
                //DB::commit();
                return response()->json(['msg' => $jsonResponse['msg']], 300);
            }
        } catch (\Exception $e) {
            Log::debug("step 3 error", [$e]);
            //DB::rollBack();
            return response()->json(['msg' => 'Database update failed', 'ex' => $e->getMessage()], 500);
        }
    }

    public function step_three(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ], [
                'company_id.required' => 'Patsanstha ID is required.',
                'company_id.exists' => 'Patsanstha ID does not exist.',
                'company_id.string' => 'Patsanstha ID must be a string.',
            ]);

            $companyId = $request->company_id;
            $company = Company::where('id', $companyId)->first();
            $database = CompanyDatabase::where('company_id', $company->id)->first();


            $response = Http::asMultipart()->post("$this->baseUrl/step3.php", [
                'company_id' => $company->company_code,
                'db_name' => $database && isset($database->db_name) ? $database->db_name : '#NA#',
                'db_user' => $database && isset($database->db_name) ? $database->db_username : '#NA#',
                'db_password' => $database && isset($database->db_name) ? $database->db_password : '#NA#',
            ]);

            if ($response->failed()) {
                return response()->json(['error' => "Failed to process your request at the moment..."], 422);
            }

            $jsonResponse = $response->json();

            //DB::beginTransaction();

            $siteSetup = SiteSetupStep::where('company_id', $company->id)
                ->where('step_name', 'step_three')
                ->first();

            if ($jsonResponse['code'] === 1) {
                $siteSetup->created_at = date('Y-m-d H:i:s');
                $siteSetup->completed_at = date('Y-m-d H:i:s');
                $siteSetup->status = 'complete';
                $siteSetup->save();
                //commit changes
                // DB::commit();
                //return response
                return response()->json(['step' => 'three', 'msg' => $jsonResponse['msg']], 200);
            } else {
                $siteSetup->request_data = json_encode($request->all());
                $siteSetup->response_data = json_encode($jsonResponse);
                $siteSetup->save();
                //DB::commit();
                return response()->json(['msg' => $jsonResponse['msg']], 300);
            }
        } catch (\Exception $e) {
            Log::debug("step 3 error", [$e]);
            //DB::rollBack();
            return response()->json(['msg' => 'Database update failed', 'ex' => $e->getMessage()], 500);
        }
    }

    public function step_four(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ]);

            $companyId = $request->company_id;
            $company = Company::where('id', $companyId)->first();
            $database = CompanyDatabase::where('company_id', $companyId)->first();

            $socialDetails = CompanySocialMediaSetting::where('company_id', $companyId)->pluck('social_media_app_id')->toArray();

            $socialDetails = $socialDetails ?? [];

            $subscriptionPurchase = SubscriptionPurchase::where('company_id', $companyId)->first();

            Log::debug("com", ["comcode" => $company->company_code]);

            $response = Http::asForm()->post("$this->baseUrl/step4.php", [
                'company_id' => $company->id,
                'company_code' => $company->company_code,
                'company_key' => $company->company_key,
                'db_name' => $database && isset($database->db_name) ? $database->db_name : '#NA#',
                'db_user' => $database && isset($database->db_username) ? $database->db_username : '#NA#',
                'db_password' => $database && isset($database->db_password) ? $database->db_password : '#NA#',
                'company_name' => $company->company_name,
                'legal_type' => $company->legal_type ?? "",
                'description' => $company->description ?? "",
                'industry_type' => $company->industry_type ?? "",
                'trade_name' => $company->trade_name ?? "",
                'reg_number' => $company->reg_number ?? "",
                'gst_number' => $company->gst_number ?? "",
                'email' => $company->email,
                'phone' => $company->phone,
                'website' => $company->website,
                'primary_contact_name' => $company->primary_contact_name ?? "",
                'primary_contact_email' => $company->primary_contact_email ?? "",
                'office_address_line_one' => $company->office_address_line_one ?? "",
                'office_address_line_two' => $company->office_address_line_two ?? "",
                'office_address_city' => $company->office_address_city ?? "",
                'office_address_province_state' => $company->office_address_province_state ?? "",
                'office_address_country_code' => $company->office_address_country_code ?? "",
                'office_address_postal_code' => $company->office_address_postal_code ?? "",
                'is_billing_address_same' => $company->is_billing_address_same,
                'billing_address_line_one' => $company->billing_address_line_one ?? "",
                'billing_address_line_two' => $company->billing_address_line_two ?? "",
                'billing_address_city' => $company->billing_address_city ?? "",
                'billing_address_province_state' => $company->billing_address_province_state ?? "",
                'billing_address_country_code' => $company->billing_address_country_code ?? "",
                'billing_address_postal_code' => $company->billing_address_postal_code ?? "",
                'account_status' => $company->account_status,
                'company_logo' => $company->company_logo ?? "",
                'is_active' => 1,
                'is_verified' => 1,
                'created_at' => $company->created_at,
                'updated_at' => $company->updated_at,
                'deleted_at' => $company->deleted_at,
                'password' => Hash::make($company->password),
                'company_country_code' => $company->company_country_code ?? "",
                'reason' => $company->reason ?? "",
                'phone_country' => $company->phone_country ?? "",
                //social media aaps ids
                'social_media_app_ids' => $socialDetails,
                //subscription details
                'subscription_id' => $subscriptionPurchase->subscription_id ?? '',
                'subscription_title' => $subscriptionPurchase->subscription_title ?? '',
                'subscription_months' => $subscriptionPurchase->subscription_months ?? '',
                'subscription_per_month_price' => $subscriptionPurchase->subscription_per_month_price ?? '',
                'subscription_total_price' => $subscriptionPurchase->subscription_total_price ?? '',
                'subscription_purchase_id' => $subscriptionPurchase->subscription_purchase_id ?? '',
                'from_date' => $subscriptionPurchase->from_date ?? '',
                'to_date' => $subscriptionPurchase->to_date ?? '',
                'currency_code' => $subscriptionPurchase->currency_code ?? '',
                'status' => $subscriptionPurchase->status ?? '',
                'payment_status' => $subscriptionPurchase->payment_status ?? '',
                'payment_order_id' => $subscriptionPurchase->payment_order_id ?? '',
                'payment_id' => $subscriptionPurchase->payment_id ?? '',
                'payment_mode' => $subscriptionPurchase->payment_mode ?? '',
                'discount_type' => $subscriptionPurchase->discount_type ?? '',
                'discount_value' => $subscriptionPurchase->discount_value ?? '',
            ]);

            if ($response->failed()) {
                return response()->json(['error' => "Failed to process your request at the moment..."], 422);
            }

            $jsonResponse = $response->json();

            //DB::beginTransaction();

            $siteSetup = SiteSetupStep::where('company_id', $company->id)
                ->where('step_name', 'step_four')
                ->first();

            if ($jsonResponse['code'] === 1) {
                $siteSetup->created_at = date('Y-m-d H:i:s');
                $siteSetup->completed_at = date('Y-m-d H:i:s');
                $siteSetup->status = 'complete';
                $siteSetup->save();
                // update database
                $company->company_code = strtoupper($company->company_code);
                $company->setup_status = 2;
                $company->save();
                //commit changes
                // DB::commit();
                //return response
                return response()->json(['step' => 'four', 'msg' => $jsonResponse['msg'], "comcode" => $company->company_code], 200);
            } else {
                $siteSetup->request_data = json_encode($request->all());
                $siteSetup->response_data = json_encode($jsonResponse);
                $siteSetup->save();
                //DB::commit();
                return response()->json(['msg' => $jsonResponse['msg'], "comcode" => $company->company_code], 300);
            }
        } catch (\Exception $e) {
            Log::debug("step 4 error", [$e]);
            //DB::rollBack();
            return response()->json(['msg' => 'Database update failed', 'ex' => $e->getMessage()], 500);
        }
    }

    public function step_five(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ]);

            $companyId = $request->company_id;
            $company = Company::where('id', $companyId)->first();
            $database = CompanyDatabase::where('company_id', $companyId)->first();

            // DB::beginTransaction();

            $siteSetup = SiteSetupStep::where('company_id', $company->id)
                ->where('step_name', 'step_five')
                ->first();
            if (!empty($siteSetup)) {
                $siteSetup->created_at = date('Y-m-d H:i:s');
                $siteSetup->completed_at = date('Y-m-d H:i:s');
                $siteSetup->status = 'complete';
                $siteSetup->save();
                // update database
                //$company->setup_status = 2;
                //$company->save();
                //send mail to company for onboarding complete


                if ($company->email != "") {
                    try{
                        $email = $company->email;
                        $company_url = env('ADMIN_API_URL') . strtolower($company->company_code);
                        $details = [
                            'title' => 'Mail from EngageReward',
                            'url' => $company_url,
                            'name' => $company->company_name,
                            'user_id' => 'admin@engagereward.com',
                            'password' => 'password@123'
                        ];
                        Mail::to($email)->send(new \App\Mail\CompanyEmail($details));
                    }catch (\Throwable $mailException) {

                        Log::error('Mail Error', [
                            'company_id' => $company->id,
                            'error'      => $mailException->getMessage()
                        ]);

                        return response()->json([
                            'step' => 'five',
                            'msg'  => getMailErrorMessage($mailException)
                        ], 500);
                    }
                }
                //commit changes
                // DB::commit();
                //return response
                return response()->json(['step' => 'five', 'msg' => "Step five completed."], 200);
            }
        } catch (\Throwable $e) {
            //DB::rollBack();
            return response()->json(['msg' => 'Cron job setup failed', 'ex' => $e->getMessage()], 500);
        }
    }
}
