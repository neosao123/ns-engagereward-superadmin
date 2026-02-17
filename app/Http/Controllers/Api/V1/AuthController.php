<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
// Models
use App\Models\Company;
use App\Models\Country;
use App\Models\SocialMediaApp;
use App\Models\CompanySocialMediaSetting;
use App\Http\Responses\StoreResponse;
use App\Http\Responses\UpdateResponse;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use App\Traits\HandlesApiResponses;

class AuthController extends Controller
{

    use HandlesApiResponses;

 //super admin login api
    public function login(Request $request)
    {
        try {
            // Validation
            $rules = [
                'company_code' => 'required',
                'email' => ['required', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'max:255'],
                'password' => ['required'],
            ];

            $messages = [
                'company_code.required' => __('api.company_code_required'),
                'email.required' => __('api.profile_email_required'),
                'email.regex' => __('api.profile_email_regex'),
                'email.max' => __('api.profile_email_max'),
                'password.required' => __('api.register_password_required'),
               // 'password.string' => __('api.register_password_string'),
               // 'password.min' => __('api.register_password_min'),
                //'password.max' => __('api.register_password_max'),
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => $validator->errors()->first()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Find company
            $company = Company::where("company_key", $request->company_code)->first();
            if (!$company) {
                return response()->json(['status' => Response::HTTP_NOT_FOUND, 'message' => __('api.company_not_found'), 'data' => null], Response::HTTP_NOT_FOUND);
            }

            if ($company->is_suspend == "1") {
                return response()->json(['status' => Response::HTTP_UNAUTHORIZED, 'message' => __('api.company_suspended'), 'data' => null], Response::HTTP_UNAUTHORIZED);
            }

            $baseUrl = rtrim(env('ADMIN_API_URL'), '/') . '/' . strtolower($company->company_code) . '/api/' . env('API_VERSION');
            $adminApiUrl = $baseUrl . '/login';

            // Make HTTP request without throw()
            $response = Http::asForm()->post($adminApiUrl, [
                'email' => $request->email,
                'password' => $request->password,
                'company_code' => $request->company_code
            ]);

            // Handle failed responses
            if ($response->failed()) {
                $res = $response->json();
                return response()->json([
                    'status' => $response->status(),
                    'message' => $res['message'] ?? 'Invalid credentials',

                    'data' => (object) []
                ], $response->status());
            }

            // Success
            $subResponse = $response->json();
            $subResponse['base_url'] = $baseUrl;

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => $subResponse['message'] ?? 'Login successful',
                'data' => $subResponse['data'] ?? (object) [],
                'base_url' => $subResponse['base_url']
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.exception_message'),
                'data' => (object) [],
                'error' => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //forget password
    public function forget_password(Request $request)
    {

        try {
            // Validate input
            $rules = [
                'company_code' => 'required|string',
                'email' => 'required|email|max:255',
            ];

            $messages = [
                'company_code.required' => __('api.company_code_required'),
                'company_code.string' => __('api.company_code_string'),
                'email.required' => __('api.email_required'),
                'email.email' => __('api.email_invalid'),
                'email.max' => __('api.email_max'),
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => $validator->errors()->first()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Find company
            $company = Company::where("company_key", $request->company_code)->first();
            if (!$company) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => __('api.company_not_found'),
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }

            if ($company->is_suspend == "1") {
                return response()->json([
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'message' => __('api.company_suspended'),
                    'data' => null
                ], Response::HTTP_UNAUTHORIZED);
            }

            $baseUrl = rtrim(env('ADMIN_API_URL'), '/') . '/' . strtolower($company->company_code) . '/api/' . env('API_VERSION');
            $adminApiUrl = $baseUrl . '/forget-password';

            // Call admin forget-password API
            $response = Http::asForm()->post($adminApiUrl, [
                'email' => $request->email,
                'company_code' => $request->company_code
            ]);

            // Handle failed responses gracefully
            if ($response->failed()) {
                $res = $response->json();
                return response()->json([
                    'status' => $response->status(),
                    'message' => $res['message'] ?? 'Unable to process request',
                    'data' => null
                ], $response->status());
            }

            // Success
            $subResponse = $response->json();
            $subResponse['base_url'] = $baseUrl;

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => $subResponse['message'] ?? 'Password reset link sent',
                'data' => $subResponse['data'] ?? [],
                'base_url' => $subResponse['base_url']
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.exception_message'),
                'data' => null,
                'error' => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function register(Request $request)
    {
        try {


            $rules = [
                'invitation_code' => 'required'
            ];

            $messages = [
                'invitation_code.required' => __('api.company_code_required')
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => $validator->errors()->first()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Find company
            $company = Company::where("company_key", $request->invitation_code)->first();
            if (!$company) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => __('api.company_not_found'),
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }

            if ($company->is_suspend == "1") {
                return response()->json([
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'message' => __('api.company_suspended'),
                    'data' => null
                ], Response::HTTP_UNAUTHORIZED);
            }

            $baseUrl = rtrim(env('ADMIN_API_URL'), '/') . '/' . strtolower($company->company_code) . '/api/' . env('API_VERSION');
            $adminApiUrl = $baseUrl . '/register';

            // Call admin forget-password API
            $response = Http::asForm()->post($adminApiUrl, [
                'name' => $request->name,
                'email' => $request->email,
                'phone_country' => $request->phone_country,
                'invitation_code' => $request->invitation_code,
                'phone' => $request->phone,
                'password' => $request->password,
            ]);

            // Handle failed responses gracefully
            if ($response->failed()) {
                $res = $response->json();
                return response()->json([
                    'status' => $response->status(),
                    'message' => $res['message'] ?? 'Unable to process request',
                    'data' => null
                ], $response->status());
            }

            // Success
            $subResponse = $response->json();
            $subResponse['base_url'] = $baseUrl;

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => $subResponse['message'] ?? '',
                'data' => $subResponse['data'] ?? [],
                'base_url' => $subResponse['base_url']
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.exception_message'),
                'data' => null,
                'error' => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


     public function reset_password(Request $request)
    {
        try {

            $rules = [
                'token' => 'required',
                'company_code' => 'required',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    //'max:20',
                    'confirmed',
                    //'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&(),.?":{}|<>_\-+=~`\/\[\]])[A-Za-z\d!@#$%^&(),.?":{}|<>_\-+=~`\/\[\]]{8,20}$/'
                ],
                'password_confirmation' => 'required|string|min:1|max:20'
            ];

            $messages = [
                'token.required' => __('api.token_required'),
                'company_code.required' => __('api.company_code_required'),
                'company_code.exists' => __('api.company_not_found'),
                'password.required' => __('api.password_required'),
                'password.string' => __('api.password_string'),
                'password.min' => __('api.password_min'),
                //'password.max' => __('api.password_max'),
                'password.confirmed' => __('api.password_mismatch'),
                //'password.regex' => __('api.password_complexity'),
                'password_confirmation.required' => __('api.confirm_password_required')
            ];

            // Validate request
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                LogHelper::logError('validation', __('api.validation_error'), $validator->errors()->first(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                return response()->json([
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => $validator->errors()->first()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Find company
            $company = Company::where('company_key', $request->company_code)->first();
            if (!$company) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => __('api.company_not_found'),
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }

            if ($company->is_suspend == "1") {
                return response()->json([
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'message' => __('api.company_suspended'),
                    'data' => null
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Build tenant base URL
            $baseUrl = rtrim(env('ADMIN_API_URL'), '/') . '/' . strtolower($company->company_code) . '/api/' . env('API_VERSION');
            $adminApiUrl = $baseUrl . '/reset-password';

            // Call tenant reset-password API
            $response = Http::asForm()->post($adminApiUrl, [
                'token' => $request->token,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'company_code' => $company->company_code
            ]);

            // Handle failure
            if ($response->failed()) {
                $res = $response->json();
                return response()->json([
                    'status' => $response->status(),
                    'message' => $res['message'] ?? 'Unable to process request',
                    'data' => null
                ], $response->status());
            }

            // Success
            $subResponse = $response->json();
            $subResponse['base_url'] = $baseUrl;

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => $subResponse['message'] ?? __('api.password_reset_success'),
                'data' => $subResponse['data'] ?? new \stdClass(),
                'base_url' => $subResponse['base_url']
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            LogHelper::logError('exception', 'API => Reset Password', $exception->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.exception_message'),
                'data' => null,
                'error' => config('app.debug') ? $exception->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function check_company_code(Request $request)
    {
        try {
            $rules = [
                'company_code' => 'required',
            ];
            $messages = [
                'company_code.required' => __('api.company_code_required'),
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => $validator->errors()->first()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Find company
            $companyCode = strtoupper($request->company_code);
            $company = Company::where("company_code", $companyCode)->first();
            if (!$company) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => __('api.company_not_found'),
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }

            if ($company->is_suspend == "1") {
                return response()->json([
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'message' => __('api.company_suspended'),
                    'data' => null
                ], Response::HTTP_UNAUTHORIZED);
            }

            $baseUrl = rtrim(env('ADMIN_API_URL'), '/') . '/' . strtolower($company->company_unique_code) . '/api/' . env('API_VERSION');

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Company verified successfully',
                'data' => $company,
                'base_url' => $baseUrl
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            LogHelper::logError('exception', 'API => Check Company Code', $exception->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.exception_message'),
                'data' => null,
                'error' => $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    

    //update version of app
    public function update_version(Request $r)
    {
        try {
            $input = $r->all();
            $validator = Validator::make($input, [
                // Setting Details
                'setting_id' => 'required',
                'setting_value' => 'required',
                'is_update_compulsory' => 'required'
            ], [

                'setting_id.required' => __('api.setting_id_required'),
                'setting_id.integer' => __('api.setting_id_integer'),
                'setting_value.required' => __('api.setting_value_required'),
                'is_update_compulsory.required' => __('api.is_update_compulsory_required'),
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                // Return error response with validation message
                $response = [
                    "status" => 500,
                    "message" => $validator->errors()->first()
                ];
                return response()->json($response, 200);
            }
            //get setting value
            $result = Setting::where("id", $r->setting_id)->first();

            if (!empty($result)) {
                $result->setting_value = $r->setting_value;
                $result->is_update_compulsory = $r->is_update_compulsory;
                $result->save();

                //success log
                LogHelper::logSuccess('The app version updated successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
                //success response
                return response()->json(["status" => 200, "message" => "Current version updated successfully."], 200);
            }

            //log error
            LogHelper::logError('An error occurred while the  app version update.', 'Failed to update app version.',  __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
            return response()->json(["status" => 300, "message" => "Failed to update current version."], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            LogHelper::logError('An error occurred while app version update.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.server_error'),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    //get version

    public function get_version(Request $r)
    {
        try {
            $data = [];
            //get ios version
            $settingIOS = Setting::select("settings.*")
                ->where("settings.id", "2")
                ->first();
            //get android version
            $settingAndroid = Setting::select("settings.*")
                ->where("settings.id", "1")
                ->first();
            if (!empty($settingAndroid)) {
                $android = [
                    "setting_id" => $settingAndroid->id,
                    "setting_name" => $settingAndroid->setting_name,
                    "setting_value" => $settingAndroid->setting_value,
                    "is_update_compulsory" => $settingAndroid->is_update_compulsory
                ];
                $data["android"] = $android;
            }
            if (!empty($settingIOS)) {
                $ios = [
                    "setting_id" => $settingIOS->id,
                    "setting_name" => $settingIOS->setting_name,
                    "setting_value" => $settingIOS->setting_value,
                    "is_update_compulsory" => $settingIOS->is_update_compulsory
                ];
                $data["ios"] = $ios;
            }
            //success log
            LogHelper::logSuccess('the app version get successfully', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            //success response

            return response()->json(["status" => 200, "message" => __('api.current_version_update'), "result" => $data], 200);
        } catch (\Exception $e) {
            // Log the error and return a generic error response
            LogHelper::logError('An error occurred while get app version.', $e->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, "");
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.server_error'),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
