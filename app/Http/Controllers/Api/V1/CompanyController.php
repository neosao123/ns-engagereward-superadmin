<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

// Models
use App\Models\Company;
use App\Models\Country;
use App\Models\SocialMediaApp;
use App\Models\CompanySocialMediaSetting;
use App\Http\Responses\StoreResponse;
use App\Http\Responses\UpdateResponse;
use Illuminate\Support\Facades\Hash;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use App\Traits\ApiSecurityTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
	use ApiSecurityTrait;

    /**
     * Update company details API
     *
     * This API validates the request, checks if the company exists,
     * and updates the company details (website, office address line one,
     * office address line two, and office city).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_company_details(Request $request)
    {
        DB::beginTransaction();
        try {
          
            $data = $this->validateApiRequest($request);
            if ($data instanceof JsonResponse) {
               
                return $data;
            }

            $company = Company::where("id",$data["company_id"])->first();
            
            if (!$company) {
                return response()->json([
                    'status'  => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Company not found.'
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            $company->company_name                  = $data['company_name'];
			
			$company->phone_country                 = strtoupper($data['phone_country']);
			$company->phone                         =$data["phone"];
			$company->website                       = $data['website'];			
			$company->office_address_line_one       = $data['office_address_line_one'];
			$company->office_address_line_two       = $data['office_address_line_two'];
			$company->office_address_city           = $data['office_address_city'];
			$company->office_address_province_state = $data['office_address_province_state'];
			$company->office_address_country_code   = $data['office_address_country_code'];
			$company->office_address_postal_code    = $data['office_address_postal_code'];

			$company->billing_address_line_one      = $data['billing_address_line_one'];
			$company->billing_address_line_two      = $data['billing_address_line_two'];
			$company->billing_address_city          = $data['billing_address_city'];
			$company->billing_address_province_state= $data['billing_address_province_state'];
			$company->billing_address_country_code  = $data['billing_address_country_code'];
			$company->billing_address_postal_code   = $data['billing_address_postal_code'];

			$company->is_billing_address_same       = $data['is_billing_address_same'];

            $path="";
            if ($request->hasFile('company_logo')) {
                // Delete old logo if it exists
                if ($company->company_logo) {
                    Storage::disk('public')->delete($company->company_logo);
                }
                
                $file = $request->file('company_logo');
                $path = Storage::disk('public')->putFileAs('company/logo', $file, "company-logo-" . time() . "." . $file->getClientOriginalExtension());
                $company->company_logo = $path;
            }

			$company->save();
			DB::commit();
            LogHelper::logSuccess('success', 'API => Company updated successfully.',  __FUNCTION__, basename(__FILE__), __LINE__, "");
            return response()->json([
                'message' => "Company updated successfully",
                'status'  => Response::HTTP_OK,
                'logo_url' => $company->company_logo ? url('storage-bucket?path=' . $company->company_logo) : null,
            ]);
            
        } catch (\Exception $exception) {
            DB::rollBack();
            LogHelper::logError(
                'exception',
                'Failed to update company details.',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ''
            );
			
			return response()->json([
				'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
				'message' => __('api.server_error'),
				'error' => config('app.debug') ?  $exception->getMessage() : null
			], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


     /**
     * Update company status for suspend/reactivate API
     *
     * This API validates the request, checks if the company exists,
     * and updates the company status (suspended or active).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_company_status(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->validateApiRequest($request);
            if ($data instanceof JsonResponse) {
                return $data;
            }

            $company = Company::where("id",$data["company_id"])->first();

            if (!$company) {
                return response()->json([
                    'status'  => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Company not found.'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $company->is_active = $data['is_active'];
            $company->save();

            DB::commit();
            LogHelper::logSuccess('success', 'API => Company status updated successfully.',  __FUNCTION__, basename(__FILE__), __LINE__, "");
            return response()->json([
                'message' => "Company status updated successfully",
                'status'  => Response::HTTP_OK,
            ]);

        } catch (\Exception $exception) {
            DB::rollBack();
            LogHelper::logError(
                'exception',
                'Failed to update company status.',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ''
            );

            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.server_error'),
                'error' => config('app.debug') ?  $exception->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}