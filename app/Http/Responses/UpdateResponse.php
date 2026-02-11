<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Support\Responsable;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\CompanySocialMediaSetting;
use App\Helpers\LogHelper;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

use App\Traits\HandlesAdminApiRequests;
use App\Traits\HandlesApiResponses;
class UpdateResponse implements Responsable
{
	use HandlesAdminApiRequests,HandlesApiResponses;
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function toResponse($r)
    {
        // Retrieve session data
        $basic_info = $r->session()->get('basic_info');
        $address_info = $r->session()->get('address_info');
        $social_info = $r->session()->get('social_info');
        $document_info = $r->session()->get('document_info', []);
        $company_id = $basic_info['company_id'] ?? null;

        if (!$company_id) {
            return response()->json(['status' => 400, 'msg' => "Missing company ID."], 400);
        }
		$e164Number="";
		if(!empty($basic_info['phone_country']) && !empty($basic_info['phone'])){
            $phoneUtil = PhoneNumberUtil::getInstance();
            $numberProto = $phoneUtil->parse($basic_info['phone'], $basic_info['phone_country']);

            //$nationalNumber = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::NATIONAL);
            $e164Number = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
        }
		//$digitsOnly = preg_replace('/\D/', '', $nationalNumber);
        //$cleanNumber = ltrim($digitsOnly, '0'); // Remove leading zero

        $data = [
            // Basic info
            "company_name" => $basic_info['company_name'],
            "company_code" => $basic_info['company_code'],
            "company_key" => $basic_info['company_code'],
            "legal_type" => $basic_info['legal_type'],
            "trade_name" => $basic_info['trade_name'],
            "company_country_code" => $basic_info['company_country_code'],
            "description" => $basic_info['description'],
            "email" => $basic_info['email'],
            //"password" => Hash::make($basic_info['password']),
            "phone" => $e164Number,
			"phone_country"=>strtoupper($basic_info['phone_country']),
            "website" => $basic_info['website'],
            "reg_number" => $basic_info['reg_number'],
            "gst_number" => $basic_info['gst_number'],
            "is_active" => $basic_info['is_active'] ?? 0,
            "is_verified" => $basic_info['is_verified'] ?? 0,

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
            "is_billing_address_same" => $address_info['is_billing_address_same'],

        ];

        if(!empty($basic_info['password'])){
            $data["password"] = Hash::make($basic_info['password']);
        }

        DB::beginTransaction();
       try {
            // File uploads


            if (!empty($basic_info["company_logo"]) && $basic_info["company_logo"]!=NULL) {
                $data["company_logo"] = $basic_info["company_logo"];
            }

            // Find and update company
            $company = Company::where('id', $company_id)->whereNull('deleted_at')->first();
            if (!$company) {
                return response()->json(['status' => 300, 'msg' => "Company not found."], 200);
            }

            $company->update($data);


            // Save Documents
			// Handle Documents Update
			foreach ($document_info as $document) {

				if ($document['id'] === "#" || empty($document['id'])) {
					// Create new document
					CompanyDocument::create([
						'company_id' => $company->id,
						'document_type' => $document['type'] ?? null,
						'document_number' => $document['number'] ?? null,
						'document_file' => $document['file_path'] ?? null,
						'is_active' => 1,
						'created_at' => now(),
						'updated_at' => now()
					]);
				} else {
					// Update existing document
					$existingDoc = CompanyDocument::find($document['id']);

					if ($existingDoc) {
						$updateData = [
							'document_type' => $document['type'] ?? $existingDoc->document_type,
							'document_number' => $document['number'] ?? $existingDoc->document_number,
							'updated_at' => now()
						];

						// Handle file update if new file path exists
						if (!empty($document['file_path']) && $document['file_path'] != $existingDoc->document_file) {
							// Delete old file if exists
							if ($existingDoc->document_file) {
								Storage::disk('public')->delete($existingDoc->document_file);
							}
							$updateData['document_file'] = $document['file_path'];
						}

						$existingDoc->update($updateData);
					}
				}
			}

            // Delete old social media links
            CompanySocialMediaSetting::where('company_id', $company->id)->delete();

            // Save updated Social Media Settings
            if ($r->session()->has('social_info')) {
                $socialInfo = $r->session()->get('social_info');
                foreach ($socialInfo as $appId => $appData) {
                    if ($appData['enabled'] ?? false) {
                        CompanySocialMediaSetting::create([
                            'company_id' => $company->id,
                            'social_media_app_id' => $appId,
                            'is_active' => 1,
                        ]);

                    }
                }
            }

            DB::commit();

            // Flash session data again
            $r->session()->flash('basic_info', $basic_info);
            $r->session()->flash('address_info', $address_info);
            $r->session()->flash('social_info', $social_info);
            $r->session()->flash('document_info', $document_info);

            LogHelper::logSuccess(
                'success',
                'Company and social media records updated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

			//sending data to admin side for update specific company

			$adminData=[
                            "company_code"=>$basic_info['company_code'],
			                "company_name" => $basic_info['company_name'],
							"legal_type" => $basic_info['legal_type'],
							"trade_name" => $basic_info['trade_name'],
							"company_country_code" => $basic_info['company_country_code'],
							"description" => $basic_info['description'],
							"email" => $basic_info['email'],
							"phone_country"=>strtoupper($basic_info['phone_country']),
							"website" => $basic_info['website'],
							 "phone" => $e164Number,
							"reg_number" => $basic_info['reg_number'],
                            "company_logo" => !empty($basic_info['company_logo']) ? url('storage-bucket?path=' . $basic_info['company_logo']) : null,
							"gst_number" => $basic_info['gst_number'],
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
							"is_billing_address_same" => $address_info['is_billing_address_same'],
							"app_info" => $social_info,
			           ];
			 if($company->company_code!="" && $company->setup_status==2){
			   $this->makeSecurePostApiRequest(strtolower($company->company_unique_code).'/api/'.env('API_VERSION').'/company-update', $adminData)->throw();

            }
             return response()->json(['status' => 200, 'msg' => "Record updated successfully."], 200);

        }  catch (RequestException $exception) {
            DB::rollBack();

            LogHelper::logError('exception', 'API => ' . __('api.super_admin_error'), $exception->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, '');
            return response()->json(['status' => 500, 'msg' => "Failed to update record.", 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            LogHelper::logError(
                'exception',
                'Failed to update company information.',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json(['status' => 500, 'msg' => "Failed to update record.", 'error' => $e->getMessage()], 500);
        }
    }

}
