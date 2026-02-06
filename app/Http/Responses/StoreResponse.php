<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Support\Responsable;
// Models
use App\Models\SiteSetupStep;
use App\Models\Company;
use App\Models\CompanySocialMediaSetting;
use App\Models\Subscription;
use App\Models\SubscriptionPurchase;
use App\Models\CompanyDocument;
use Illuminate\Support\Facades\Hash;
// Helper
use App\Helpers\LogHelper;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class StoreResponse implements Responsable
{
	protected $payload;

	public function __construct($payload)
	{
		$this->payload = $payload;
	}

	/**
	 * Create an HTTP response that represents the object.
	 *
	 * @param  \Illuminate\Http\Request  $r
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function toResponse($r)
	{
		// Retrieve session data
		$basic_info = $r->session()->get('basic_info');
		$address_info = $r->session()->get('address_info');
		$social_info = $r->session()->get('social_info');
		$sub_info = $r->session()->get('sub_info');
		$document_info = $r->session()->get('document_info', []);

        $e164Number ="";
		if(!empty($basic_info['phone'])){
			$phoneUtil = PhoneNumberUtil::getInstance();
			$numberProto = $phoneUtil->parse($basic_info['phone'], $basic_info['phone_country']);

			$e164Number = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);

		}
		//$nationalNumber = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::NATIONAL);
		//$digitsOnly = preg_replace('/\D/', '', $nationalNumber);
		//$cleanNumber = ltrim($digitsOnly, '0'); // Remove leading zero

		$data = [
			// basic info
			"company_code"=>$basic_info['company_code'],
			"company_key" => $basic_info['company_key'],
			"company_unique_code" => $basic_info['company_unique_code'],
			"company_name" => $basic_info['company_name'],
			"legal_type" => $basic_info['legal_type'],
			"trade_name" => $basic_info['trade_name'],
			"company_country_code" => $basic_info['company_country_code'],
			"description" => $basic_info['description'],
			"email" =>  $basic_info['email'],
			"password" => "Passord@123",
			"phone" => $e164Number,
			"phone_country" => strtoupper($basic_info['phone_country']),
			"website" => $basic_info['website'],
			"reg_number" => $basic_info['reg_number'],
			"gst_number" => $basic_info['gst_number'],
			"subscription_id" => $sub_info['subscription'],
			"account_status" => "active",
			"is_active" => $basic_info['is_active'] ? $basic_info['is_active'] : 0,

			// Address Details
			"office_address_line_one" => $address_info['office_address_line_one'],
			"office_address_line_two" => $address_info['office_address_line_two'],
			"office_address_city" => $address_info['office_address_city'],
			"office_address_province_state" => $address_info['office_address_province_state'],
			"office_address_country_code" => $address_info['office_address_country_code'],
			"office_address_postal_code" => $address_info['office_address_postal_code'],
			"billing_address_line_one" => $address_info['billing_address_line_one'],
			"billing_address_line_two" => $address_info['billing_address_line_two'],
			"billing_address_city" => $address_info['billing_address_city'],
			"billing_address_province_state" => $address_info['billing_address_province_state'],
			"billing_address_country_code" => $address_info['billing_address_country_code'],
			"billing_address_postal_code" => $address_info['billing_address_postal_code'],
			"billing_address_postal_code" => $address_info['billing_address_postal_code'],
			"is_billing_address_same" => $address_info['is_billing_address_same'],
		];

		DB::beginTransaction();
		try {
			// File uploads
			if (!empty($basic_info["company_logo"])) {
				$data["company_logo"] = $basic_info["company_logo"];
			}

			// Insert data
			$company = Company::create($data);

			$subscriptionData=Subscription::where("id",$sub_info['subscription'])->first();

			$startDate = now();
			$toDate = $startDate->copy()->addMonths($subscriptionData->subscription_months);

			$subscription_plan=[
			                "company_id"=>$company->id,
							"subscription_id"=>$sub_info['subscription'],
			                "subscription_title"=>$subscriptionData->subscription_title,
							"subscription_months"=>$subscriptionData->subscription_months,
							"subscription_per_month_price"=>$subscriptionData->subscription_per_month_price,
							"subscription_total_price"=>$subscriptionData->subscription_total_price,
							"from_date"=>$startDate,
							"to_date"=>$toDate,
                            "payment_status"=>"paid",
							"discount_type"=>$subscriptionData->discount_type,
							"discount_value"=>$subscriptionData->discount_value,
							"currency_code"=>$subscriptionData->currency_code,
                            "subscription_purchase_id"=>1
						];

			SubscriptionPurchase::create($subscription_plan);

			// Save Documents
			foreach ($document_info as $document) {
				CompanyDocument::create([
					'company_id' => $company->id,
					'document_type' => $document['type'] ?? null,
					'document_number' => $document['number'] ?? null,
					'document_file' => $document['file_path'] ?? null,
					'is_active' => 1,
					'created_at' => now(),
					'updated_at' => now()
				]);
			}

			// Save Social Media Settings
			// Inside toResponse() method:
			if ($r->session()->has('social_info')) {
				$socialInfo = $r->session()->get('social_info');

				foreach ($socialInfo as $appId => $appData) {
					if ($appData['enabled'] ?? false) {
						CompanySocialMediaSetting::create([
							'company_id' => $company->id,
							'social_media_app_id' => $appId,
							'is_active' => 1
						]);
					}
				}
			}

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


			DB::commit();

			// Flash Session Data
			$r->session()->flash('basic_info', $basic_info);
			$r->session()->flash('address_info', $address_info);
			$r->session()->flash('social_info', $social_info);
			$r->session()->flash('sub_info', $sub_info);
			$r->session()->flash('document_info', $document_info);
			// Log success
			LogHelper::logSuccess(
				'success',
				'Company and social media records created successfully.',
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				$r->path(),
				Auth::guard('admin')->user()->id ?? null
			);

			return response()->json(['status' => 200, 'msg' => "Record added successfully.", 'data' => $data], 200);
		} catch (\Exception $e) {

			// Log error
			LogHelper::logError(
				'exception',
				'Failed to save company information.',
				$e->getMessage(),
				__FUNCTION__,
				basename(__FILE__),
				__LINE__,
				$r->path(),
				Auth::guard('admin')->user()->id ?? null
			);
			DB::rollBack();
			return response()->json(['status' => 500, 'msg' => "Failed to add record.", 'error' => $e->getMessage()], 500);
		}
	}
}
