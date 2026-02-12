<?php

/** --------------------------------------------------------------------------------
 * This controller manages all the company operations
 *
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
// Helper
use App\Helpers\LogHelper;
// Models
use App\Models\Company;
use App\Models\Country;
use App\Models\SocialMediaApp;
use App\Models\CompanyDocument;
use App\Models\SiteSetupStep;
use App\Models\CompanySocialMediaSetting;
use App\Models\IntegrationCredential;
use App\Models\Subscription;
use DB;
use App\Http\Responses\StoreResponse;
use App\Http\Responses\UpdateResponse;
use PDF;
use Illuminate\Support\Str;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use App\Models\SubscriptionPurchase;

use App\Traits\HandlesAdminApiRequests;
use App\Traits\HandlesApiResponses;

class CompanyController extends Controller
{

    use HandlesAdminApiRequests, HandlesApiResponses;
    public function __construct()
    {
        // List & index
        $this->middleware('permission:Company.List,admin')->only(['index']);
        $this->middleware('permission:Company.Create,admin')->only(['create']);
        $this->middleware('permission:Company.View,admin')->only('show');
        $this->middleware('permission:Company.Edit,admin')->only(['edit']);
        $this->middleware('permission:Company.Delete,admin')->only('destroy');
    }

    /**
     * Display the index page for the company resource.
     *
     * This method handles the request to show the company index page.
     * It logs both successful access and any exceptions that occur.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        try {
            // Success log
            LogHelper::logSuccess(
                'success',
                'Company index page loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return view('main.company.index');
        } catch (\Exception $exception) {
            // Error log
            LogHelper::logError(
                'exception',
                'An error occurred while the company index page',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while opening the company index page');
        }
    }

    /**
     * Fetch and return a list of company keys based on search criteria.
     *
     * This method searches for company keys matching the input search term,
     * and returns them in a format suitable for select2 or similar dropdowns.
     *
     * @param \Illuminate\Http\Request $r The incoming request containing search parameters
     * @return \Illuminate\Http\JsonResponse JSON response containing company key data or empty array on error
     */
    public function company_key(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');

            $result = Company::where('company_key', 'like', '%' . $search . '%')
                ->whereNull("deleted_at")
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            foreach ($result as $item) {
                $html[] = ['id' => $item->id, 'text' => $item->company_key];
            }

            return response()->json($html);
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Error while fetching company key list',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return response()->json([]);
        }
    }


    /**
     * Fetch and return a list of company names based on search criteria.
     *
     * This method searches for company names matching the input search term,
     * and returns them in a format suitable for dropdowns (like select2).
     *
     * @param \Illuminate\Http\Request $r The request containing search parameters
     * @return \Illuminate\Http\JsonResponse JSON response with company names or empty array on error
     */
    public function company_name(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');

            $result = Company::where('company_name', 'like', '%' . $search . '%')
                ->whereNull("deleted_at")
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            foreach ($result as $item) {
                $html[] = ['id' => $item->id, 'text' => $item->company_name];
            }

            return response()->json($html);
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Error while fetching company name list',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return response()->json([]);
        }
    }

    /**
     * Fetch and return a list of company emails based on search criteria.
     *
     * @param \Illuminate\Http\Request $r The request containing search parameters
     * @return \Illuminate\Http\JsonResponse JSON response with company emails or empty array on error
     */
    public function company_email(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');

            $result = Company::where('email', 'like', '%' . $search . '%')
                ->whereNull("deleted_at")
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            foreach ($result as $item) {
                $html[] = ['id' => $item->id, 'text' => $item->email];
            }

            return response()->json($html);
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Error while fetching company email list',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return response()->json([]);
        }
    }


    /**
     * Fetch and return a list of subscription titles based on search criteria.
     *
     * This method is used to search subscription titles for Select2 dropdowns.
     * It returns matched subscriptions with `id` and `text` keys.
     *
     * @param \Illuminate\Http\Request $r The request containing search parameters
     * @return \Illuminate\Http\JsonResponse JSON response with subscription titles or empty array on error
     */
    public function subscription_title(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');

            // Get today's date in Y-m-d format
            $today = date('Y-m-d');

            $query = Subscription::where('subscription_title', 'like', '%' . $search . '%')
                ->where('is_active', 1)
                ->whereDate('from_date', '<=', $today)  // from_date should be <= today
                ->whereDate('to_date', '>=', $today);    // to_date should be >= today

            $result = $query->orderBy('id', 'DESC')
                //->limit(20)
                ->get();

            foreach ($result as $item) {
                $html[] = ['id' => $item->id, 'text' => $item->subscription_title];
            }

            return response()->json($html);
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Error while fetching subscription title list',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([]);
        }
    }
    /**
     * Fetch and return a list of company phone numbers based on search criteria.
     *
     * @param \Illuminate\Http\Request $r The request containing search parameters
     * @return \Illuminate\Http\JsonResponse JSON response with company phones or empty array on error
     */
    public function company_phone(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');

            $result = Company::where('phone', 'like', '%' . $search . '%')
                ->whereNull("deleted_at")
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            foreach ($result as $item) {
                $html[] = ['id' => $item->id, 'text' => $item->phone];
            }

            return response()->json($html);
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Error while fetching company phone list',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return response()->json([]);
        }
    }

    /**
     * Display a paginated listing of companies with filtering and sorting capabilities.
     *
     * This method handles DataTables server-side processing for company records,
     * including search, pagination, and action buttons with permission checks.
     *
     * @param \Illuminate\Http\Request $r The incoming request with DataTables parameters
     * @return \Illuminate\Http\JsonResponse JSON response formatted for DataTables
     */

    public function list(Request $r)
    {
        try {
            $userName = env('SUPER_ADMIN_LOGIN_USERNAME', 'superuser');
            $password = env('SUPER_ADMIN_LOGIN_PASSWORD', '12345678');

            // Social media icon mapping
            $socialIcons = [
                'facebook' => 'fab fa-facebook-f',
                'twitter' => 'fab fa-twitter',
                'instagram' => 'fab fa-instagram',
                'linkedin' => 'fab fa-linkedin-in',
                'youtube' => 'fab fa-youtube',
                'tiktok' => 'fab fa-tiktok',
                'snapchat' => 'fab fa-snapchat-ghost',
                'sharechat' => 'fas fa-share-alt-square',
                'whatsapp' => 'fab fa-whatsapp',
                'pinterest' => 'fab fa-pinterest-p',
                'reddit' => 'fab fa-reddit-alien',
            ];

            // Get pagination parameters
            $limit = $r->length;
            $offset = $r->start;
            $search = $r->input('search.value') ?? "";
            $company_key = $r->company_key ?? "";
            $company_name = $r->company_name ?? "";
            $phone = $r->phone ?? "";
            $email = $r->email ?? "";

            // Get filtered data
            $filteredData = Company::filterCompany($search, $limit, $offset, $company_key, $company_name, $email, $phone);
            $total = $filteredData['totalRecords'];
            $records = $filteredData['result'];

            $data = [];
            $srno = $offset + 1;

            // Check permissions
            $canViewAction = Auth::guard('admin')->user()->canany([
                'Company.View',
                'Company.Edit',
                'Company.Delete'
            ]);

            if ($records->count() > 0) {
                foreach ($records as $row) {

                    $loginUrl = env('ADMIN_API_URL') . strtolower($row->company_unique_code);
                    $formattedDate = \Carbon\Carbon::parse($row->created_at)->format('d-m-Y');

                    // Status badges
                    $status = $row->is_active == 1
                        ? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
                        : '<div><span class="badge rounded-pill badge-soft-danger">In-Active</span></div>';

                    $account_status = '';
                    if ($row->account_status == "active") {
                        $account_status = '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>';
                    } elseif ($row->account_status == "suspended") {
                        $account_status = '<div><span class="badge rounded-pill badge-soft-danger">Suspended</span></div>';
                    }

                    // Get social details (commented out in original)
                    $getSocialDetails = $this->social_information($row->id);

                    $compan_code = $row->company_code;
                    $company_name = $row->company_name;

                    // Action buttons
                    $action = '';
                    if ($canViewAction) {


                        $action = '
                        <span>
                            <div class="dropdown font-sans-serif position-static">
                                <button class="btn btn-link text-600 btn-sm btn-reveal" type="button" data-bs-toggle="dropdown" data-boundary="window"
                                    aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end border py-0">
                                    <div class="bg-white py-2">';

                        // View button
                        if (Auth::guard('admin')->user()->can('Company.View')) {
                            $action .= '<a class="dropdown-item" href="' . url('company/' . $row->id) . '"> <i class="fas fa-eye"></i> View</a>';
                        }

                        // Edit button
                        if (Auth::guard('admin')->user()->can('Company.Edit')) {
                            $action .= '<a class="dropdown-item btn-edit" href="' . url('company/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> Edit</a>';
                        }

                        // Delete button
                        if (Auth::guard('admin')->user()->can('Company.Delete')) {
                            $action .= '<a class="dropdown-item btn-delete" style="cursor: pointer;" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> Delete</a>';
                        }

                        // Account action button

                        if (Auth::guard('admin')->user()->can('Company.Status-Change')) {
                            if ($row->company_code != "" && $row->setup_status == 2) {
                                $action .= '<a class="dropdown-item btn-account-action" style="cursor: pointer;" data-id="' . $row->id . '"><i class="fas fa-check"></i> Account Action</a>';
                            }
                        }

                        if ($row->setup_status == 2) {
                            $action .= '<a class="dropdown-item btn-copy-url" data-url="' . $loginUrl . '" style="cursor:pointer;"><i class="fas fa-link me-2"></i>' . __('index.login_url') . '</a>';
                            $cmCode = strtolower($row->company_code);
                           $baseUrl = rtrim(env('ADMIN_API_URL'), '/');

                            $url = $baseUrl . "/{$cmCode}/system-login"
                                 . "?u={$userName}&p={$password}&redirect=dashboard";
                            $action .= '<a class="dropdown-item" href="' . $url . '" target="_blank"><i class="fas fa-external-link-alt me-2"></i> Direct Login</a>';

                            $compan_code = '<a class="text-primary" href="' . $url . '" target="_blank" style="cursor:pointer;text-decoration:underline">' . $row->company_code . '</a>';

                            $company_name = '<a class="text-primary" href="' . $url . '" target="_blakn" style="cursor:pointer;text-decoration:underline">' . $row->company_name . '</a>';
                        }

                        /*
                        if(count($getSocialDetails) > 0) {
							foreach($getSocialDetails as $social) {
								$socialName = strtolower($social['name']);

								// Special handling for platform name variations
								$iconClass = 'fas fa-share-alt'; // default icon
								foreach($socialIcons as $platform => $icon) {
									if(strpos($socialName, $platform) !== false) {
										$iconClass = $icon;
										break;
									}
								}
								$action .= '<a class="dropdown-item" href="' . url('company/integration-credentials/' . $social["id"] . '/' . $row->id . '/' .$social["name"]. '/add') . '">
											  <i class="' . $iconClass . ' me-2"></i> ' . htmlspecialchars($social["name"]) . ' Link
										   </a>';
							}
						}
                        */

                        // Social platform integration link
                        $action .= '<a class="dropdown-item" href="' . url('company/integration-credentials/' . $row->id . '/add') . '">
                                  <i class="fas fa-share-alt me-2"></i> Social Platform Integration
                               </a>';

                        // if ($row->setup_status !== 2) {
                            $action .= '<a class="dropdown-item btn-edit text-success" href="' . url('company/' . $row->id . '/setup') . '"><i class="fas fa-magic"></i> Site Setup </a>';
                        // }

                        $action .= '</div></div></div></span>';
                    }

                    // Build row data
                    $rowData = [];

                    // Add action column only if permission allows
                    if ($canViewAction) {
                        $rowData[] = $action;
                    }


                    $rowData[] = $compan_code;
                    $rowData[] = $company_name;
                    $rowData[] = $row->email ?? "";
                    $rowData[] = $row->phone ?? "";
                    $rowData[] = $account_status;
                    $rowData[] = $status;

                    $data[] = $rowData;
                    $srno++;
                }
            }

            return response()->json([
                "draw" => intval($r->draw),
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $data
            ], 200);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while fetching the company list.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path() ?? '',
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                "message" => "An error occurred while fetching the company list",
            ], 500);
        }
    }

    /**
     * Fetch and return a list of active countries matching search criteria.
     *
     * This method provides an autocomplete/search functionality for countries,
     * returning results in a format suitable for select dropdowns.
     *
     * @param \Illuminate\Http\Request $r The request containing search parameters
     * @return \Illuminate\Http\JsonResponse JSON response with country data or empty array on error
     */
    public function country_list(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');
            $result = Country::where('country_name', 'like', '%' . $search . '%')
                ->where("is_active", 1)
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            if ($result) {
                foreach ($result as $item) {
                    $html[] = ['id' => $item->country_short_code, 'text' => $item->country_name];
                }
            }
            return response()->json($html);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while the fetching country list',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return response()->json([]);
        }
    }


    /**
     * Display the company creation form.
     *
     * This method loads the view for creating a new company,
     * including any required data like active social media apps.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        try {
            $socialApps = SocialMediaApp::whereNull("deleted_at")->where('is_active', 1)->orderBy("id", "DESC")->get();
            // Success log
            LogHelper::logSuccess(
                'success',
                'Company create page loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return view('main.company.add', compact('socialApps'));
        } catch (\Exception $exception) {
            // Error log
            LogHelper::logError(
                'exception',
                'An error occurred while the creating company page',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while creating company page');
        }
    }

    /**
     * Add/validate basic company information.
     *
     * This method handles the validation and temporary storage of basic company information,
     * including generating a unique company key, validating all input fields, and handling
     * the company logo upload. The validated data is stored in session for later use.
     *
     * @param \Illuminate\Http\Request $r The incoming request with company data
     * @return \Illuminate\Http\JsonResponse JSON response with validation results or success message
     */
    public function suggest_code(Request $r)
    {
        try {
            $name = $r->input('name');
            if (!$name) {
                return response()->json(['codes' => []]);
            }

            // Cleanup name to get characters only
            $cleanName = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));
            if (empty($cleanName)) {
                $cleanName = 'COMPANY';
            }

            // Base code: First 6 characters
            $baseCode = str_pad(substr($cleanName, 0, 6), 6, 'X', STR_PAD_RIGHT);

            $suggestions = [];
            $currentCode = $baseCode;
            $attempts = 0;

            // Simple loop to find 3 unique available codes starting from the base
            while (count($suggestions) < 3 && $attempts < 100) {
                $isTaken = DB::table('companies')->where('company_code', $currentCode)->whereNull('deleted_at')->exists();
                
                if (!$isTaken && !in_array($currentCode, $suggestions)) {
                    $suggestions[] = $currentCode;
                }

                $currentCode++; // Increment (e.g., NEOSAO -> NEOSAP)
                
                // Keep it exactly 6 chars
                if (strlen($currentCode) > 6) {
                    $currentCode = substr($currentCode, 0, 6);
                    // If stuck, break to avoid infinite loop
                    if ($attempts > 50) {
                         $currentCode = strtoupper(Str::random(6));
                    }
                }
                $attempts++;
            }

            return response()->json(['codes' => $suggestions]);

        } catch (\Exception $e) {
            return response()->json(['codes' => []]);
        }
    }

    /**
     * Add/validate basic company information.
     *
     * This method handles the validation and temporary storage of basic company information,
     * including generating a unique company key, validating all input fields, and handling
     * the company logo upload. The validated data is stored in session for later use.
     *
     * @param \Illuminate\Http\Request $r The incoming request with company data
     * @return \Illuminate\Http\JsonResponse JSON response with validation results or success message
     */
    public function add_basic_info(Request $r)
    {
        // Generate company key
        /*do {
            $companyKey = strtoupper(Str::random(5));
        } while (DB::table('companies')->where('company_key', $companyKey)->exists());*/
        
        $rules = [
            'company_code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[A-Z]+$/',
                Rule::unique('companies', 'company_code')->whereNull('deleted_at'),
            ],

            'company_name' => 'required|string|max:100',
            'trade_name' => "nullable|string|max:100",
            'company_country_code' => "required",
            'legal_type' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',

            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                Rule::unique('companies', 'email')->whereNull('deleted_at'),
            ],
            'phone' => [
                'nullable',
                function ($attribute, $value, $fail) use ($r) {
                    if (empty($value)) return; // Skip if phone is empty

                    $phoneUtil = PhoneNumberUtil::getInstance();
                    try {
                        $countryCode = $r->input('phone_country');
                        $numberProto = $phoneUtil->parse($value, $countryCode);

                        if (!$phoneUtil->isValidNumber($numberProto)) {
                            $fail('Invalid phone number');
                            //$fail('Invalid phone number for ' . $phoneUtil->getRegionCodeForCountryCode($numberProto->getCountryCode()));
                        }

                        // Check uniqueness for this country
                        $formattedNumber = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
                        $exists = DB::table('companies')
                            ->where('phone', $formattedNumber)
                            ->where('phone_country', $countryCode)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($exists) {
                            $fail('This phone number already exists.');
                        }
                    } catch (NumberParseException $e) {
                        $fail('Invalid phone number format for selected country');
                    }
                }
            ],

            'website' => 'nullable|url|max:255',
            'reg_number' => 'nullable|string|max:50',
            'gst_number' => [
                'nullable',
                'regex:/^(?:\\d{2}[A-Z]{5}\\d{4}[A-Z]{1}[A-Z\\d]{1}Z[A-Z\\d]{1}|[A-Z]{0,2}\\d{9,12})$/i'
            ],
            //'gst_number' => 'nullable|regex:/^\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z\d]{1}Z[A-Z\d]{1}$/',
            'account_status' => 'nullable|in:active,suspended',

            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048|dimensions:ratio=1/1',
            //'company_logo' => 'nullable|image|mimes:jpg,jpeg,png|dimensions:width=512,height=512',
            'is_active' => 'nullable|boolean',
            // 'password' => 'required|min:6|max:20|regex:/^\S+$/',
            //'password_confirmation' => 'required|same:password|regex:/^\S+$/',
        ];

        $messages = [
            'company_name.required' => 'Please enter the company name.',
            'company_code.required' => 'Company Code is required.',
            'company_code.size' => 'Company Code must be 6 characters.',
            'company_code.regex' => 'Company Code must be uppercase alphanumeric.',
            'company_code.unique' => 'This Company Code is already taken.',
            'company_name.string' => 'Company name must be a valid text.',
            'company_name.max' => 'Company name cannot exceed 100 characters.',

            'trade_name.string' => 'Trade name must be a valid text.',
            'trade_name.max' => 'Trade name cannot exceed 100 characters.',

            'company_country_code.required' => 'Please select a country',

            'legal_type.required' => 'Please select a legal type.',
            'legal_type.string' => 'Legal type must be a valid text.',
            'legal_type.max' => 'Legal type cannot exceed 100 characters.',

            'description.string' => 'Description must be a valid text.',
            'description.max' => 'Description cannot exceed 255 characters.',

            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.regex' => 'The email format is invalid. Please use format: example@domain.com',
            'email.unique' => 'This email address is already registered.',
            'email.max' => 'Email address cannot exceed 150 characters.',

            'phone.numeric' => 'Phone number must contain only digits.',
            'phone.digits_between' => 'Phone number must be between 10 to 15 digits.',
            'phone.unique' => 'This phone number is already registered.',

            'website.url' => 'Please enter a valid website URL (e.g., https://example.com).',
            'website.max' => 'Website URL cannot exceed 255 characters.',

            'reg_number.string' => 'Registration number / Trade license number must be a valid text.',
            'reg_number.max' => 'Registration number / Trade license number cannot exceed 50 characters.',

            'gst_number.regex' => 'Please enter a valid GST / VAT number format.',

            'account_status.in' => 'Please select a valid account status.',

            //'company_logo.required' => 'Please upload a company logo.',
            'company_logo.image' => 'The uploaded file must be an image.',
            'company_logo.mimes' => 'Only JPG, JPEG, and PNG images are allowed.',
            //'company_logo.dimensions' => 'Logo must be exactly 512x512 pixels.',
            'company_logo.dimensions' => 'Logo must be square (1:1 aspect ratio).',
            'company_logo.max' => 'Logo must not be larger than 2MB.',

            'is_active.boolean' => 'Please select a valid status.',

            //'password.required' => 'Please enter a password.',
            //'password.min' => 'Password must be at least 6 characters.',
            // 'password.max' => 'Password cannot exceed 20 characters.',
            //'password.regex' => 'Password cannot contain spaces.',

            //'password_confirmation.required' => 'Please confirm your password.',
            //'password_confirmation.same' => 'Passwords do not match.',
            //'password_confirmation.regex' => 'Confirm password cannot contain spaces.',
        ];

        $validator = Validator::make($r->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            // Handle logo upload
            $path = null;

            if ($r->hasFile('company_logo')) {
                $file = $r->file('company_logo');
                $path = Storage::disk('public')->putFileAs('company/logo', $file, "company-logo-" . time() . "." . $file->getClientOriginalExtension());
            }

            do {
                $companyUniqueCode = Str::upper(Str::random(10));
            } while (DB::table('companies')->where('company_unique_code', $companyUniqueCode)->exists());

            $data = [
                'company_code' => $r->company_code,
                'company_key' => $r->company_code,
                'company_unique_code' => $companyUniqueCode,
                'company_name' => $r->company_name,
                'legal_type' => $r->legal_type,
                'trade_name' => $r->trade_name,
                'company_country_code' => $r->company_country_code,
                'description' => $r->description,
                'email' => $r->email,
                'phone' => $r->phone,
                'phone_country' => $r->phone_country,
                'website' => $r->website,
                'reg_number' => $r->reg_number,
                'gst_number' => $r->gst_number,
                //'password' => $r->password,
                'company_logo' => $path,
                'is_active' => 1,
            ];

            // Store in session
            $r->session()->put('basic_info', $data);

            // Log success
            LogHelper::logSuccess(
                'success',
                'Company basic info validated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 200,
                'msg' => "Company information validated successfully.",
                'data' => $data
            ]);
        } catch (\Exception $e) {
            // Log error
            LogHelper::logError(
                'exception',
                'An error occurred while validate company basic info.',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'An error occurred while saving company info. Please try again later.',
            ]);
        }
    }

    /**
     * Validate and store company address information.
     *
     * This method handles the validation and temporary storage of both office and billing address
     * information for a company. It validates required fields and length constraints, then stores
     * the validated data in the session for later use in the company creation process.
     *
     * @param \Illuminate\Http\Request $r The incoming request with address data
     * @return \Illuminate\Http\JsonResponse JSON response with validation results or success message
     */
    public function add_address_info(Request $r)
    {
        $rules = [
            'office_address_line_one' => 'required|string|max:255',
            'office_address_line_two' => 'nullable|string|max:255',
            'office_address_city' => 'required|string|max:100',
            'office_address_province_state' => 'required|string|max:100',
            'office_address_country_code' => 'required|string',
            'office_address_postal_code' => 'required|string|max:10',

            'billing_address_line_one' => 'required|string|max:255',
            'billing_address_line_two' => 'nullable|string|max:255',
            'billing_address_city' => 'required|string|max:100',
            'billing_address_province_state' => 'required|string|max:100',
            'billing_address_country_code' => 'required|string',
            'billing_address_postal_code' => 'required|string|max:10',
        ];

        $messages = [
            // Office address required messages
            'office_address_line_one.required' => 'Office Address Line 1 is required.',
            'office_address_city.required' => 'Office City is required.',
            'office_address_province_state.required' => 'Office State/Province is required.',
            'office_address_country_code.required' => 'Office Country is required.',
            'office_address_postal_code.required' => 'Office Postal Code is required.',

            // Billing address required messages
            'billing_address_line_one.required' => 'Billing Address Line 1 is required.',
            'billing_address_city.required' => 'Billing City is required.',
            'billing_address_province_state.required' => 'Billing State/Province is required.',
            'billing_address_country_code.required' => 'Billing Country is required.',
            'billing_address_postal_code.required' => 'Billing Postal Code is required.',

            // Office address max length messages
            'office_address_line_one.max' => 'Office Address Line 1 must not exceed 255 characters.',
            'office_address_line_two.max' => 'Office Address Line 2 must not exceed 255 characters.',
            'office_address_city.max' => 'Office City must not exceed 100 characters.',
            'office_address_province_state.max' => 'Office State/Province must not exceed 100 characters.',
            'office_address_postal_code.max' => 'Office Postal Code must not exceed 10 characters.',

            // Billing address max length messages
            'billing_address_line_one.max' => 'Billing Address Line 1 must not exceed 255 characters.',
            'billing_address_line_two.max' => 'Billing Address Line 2 must not exceed 255 characters.',
            'billing_address_city.max' => 'Billing City must not exceed 100 characters.',
            'billing_address_province_state.max' => 'Billing State/Province must not exceed 100 characters.',
            'billing_address_postal_code.max' => 'Billing Postal Code must not exceed 10 characters.',
        ];

        $validator = Validator::make($r->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            $data = [
                'office_address_line_one' => $r->office_address_line_one,
                'office_address_line_two' => $r->office_address_line_two,
                'office_address_city' => $r->office_address_city,
                'office_address_province_state' => $r->office_address_province_state,
                'office_address_country_code' => $r->office_address_country_code,
                'office_address_postal_code' => $r->office_address_postal_code,

                'billing_address_line_one' => $r->billing_address_line_one,
                'billing_address_line_two' => $r->billing_address_line_two,
                'billing_address_city' => $r->billing_address_city,
                'billing_address_province_state' => $r->billing_address_province_state,
                'billing_address_country_code' => $r->billing_address_country_code,
                'billing_address_postal_code' => $r->billing_address_postal_code,

                'is_billing_address_same' => $r->has('is_billing_address_same') ? 1 : 0,
            ];

            $r->session()->put('address_info', $data);

            // Log success
            LogHelper::logSuccess(
                'success',
                'Address information validated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 200,
                'msg' => 'Address information validated successfully.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            // Log error
            LogHelper::logError(
                'exception',
                'An error occurred while validate address info.',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'An error occurred while saving address info. Please try again.',
            ]);
        }
    }




    /**
     * Validate and store social media information for a company.
     *
     * This method handles the validation and temporary storage of social media platform selections.
     * It ensures at least one platform is enabled and stores only the enabled platforms in session.
     *
     * @param \Illuminate\Http\Request $r The incoming request with social media data
     * @return \Illuminate\Http\JsonResponse JSON response with validation results or success message
     */
    public function add_social_info(Request $r)
    {
        $rules = [
            'social_apps' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // Check if at least one app is enabled
                    $atLeastOneEnabled = collect($value)->contains('enabled', 1);

                    if (!$atLeastOneEnabled) {
                        $fail('Please enable at least one social media platform.');
                    }
                }
            ],
            'social_apps.*.enabled' => 'nullable|in:1' // Only check if enabled is 1 or not
        ];

        $messages = [
            'social_apps.required' => 'Please select at least one social media platform.',
            'social_apps.*.enabled.in' => 'Invalid value for the checkbox.'
        ];

        $validator = Validator::make($r->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            // Store only enabled apps in session
            $socialData = [];
            foreach ($r->social_apps as $appId => $appData) {
                if (!empty($appData['enabled'])) {
                    $socialData[$appId] = ['enabled' => 1];
                }
            }

            $r->session()->put('social_info', $socialData);

            LogHelper::logSuccess(
                'success',
                'Social media selections stored successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 200,
                'msg' => 'Social media preferences saved.',
                'data' => $socialData
            ]);
        } catch (\Exception $e) {
            LogHelper::logError(
                'exception',
                'Failed to save social media preferences.',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'Failed to save social media preferences. Please try again.',
            ]);
        }
    }


    /**
     * Validate and store company subscription information.
     *
     * This method handles the validation and temporary storage of both office and billing address
     * information for a company. It validates required fields and length constraints, then stores
     * the validated data in the session for later use in the company creation process.
     *
     * @param \Illuminate\Http\Request $r The incoming request with subscription data
     * @return \Illuminate\Http\JsonResponse JSON response with validation results or success message
     */
    public function add_sub_info(Request $r)
    {
        $rules = [
            'subscription' => 'required'
        ];

        $messages = [

            'subscription.required' => 'Subscription is required.'
        ];

        $validator = Validator::make($r->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            $data = [
                'subscription' => $r->subscription
            ];

            $r->session()->put('sub_info', $data);

            // Log success
            LogHelper::logSuccess(
                'success',
                'Subscription information validated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 200,
                'msg' => 'Subscription information validated successfully.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            // Log error
            LogHelper::logError(
                'exception',
                'An error occurred while validate subscription info.',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'An error occurred while saving subscription info. Please try again.',
            ]);
        }
    }

    /**
     * Validate and store company document information.
     *
     * This method handles the validation and temporary storage of company documents,
     * including file uploads and document metadata. Uploaded files are stored in
     * the public storage with unique filenames.
     *
     * @param \Illuminate\Http\Request $r The incoming request with document data
     * @return \Illuminate\Http\JsonResponse|StoreResponse JSON response or StoreResponse instance
     */
    public function add_document_info(Request $r)
    {
        $rules = [
            'documents' => [
                'required',
                'array',
                function ($attribute, $value, $fail) use ($r) {
                    // Check if at least one document has an uploaded file
                    $hasFile = collect($r->documents ?? [])->contains(function ($doc) {
                        return isset($doc['file']) && $doc['file'] instanceof \Illuminate\Http\UploadedFile;
                    });

                    if (!$hasFile) {
                        $fail('At least one document is required.');
                    }
                },
            ],
            'documents.*.type' => 'nullable|string|max:255',
            'documents.*.number' => 'nullable|string|max:255',
            'documents.*.file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048'
            ]
        ];

        $messages = [
            'documents.required' => 'Please upload at least one document.',
            'documents.*.type.string' => 'Document type must be text.',
            'documents.*.type.max' => 'Document type cannot exceed 255 characters.',
            'documents.*.number.string' => 'Document number must be text.',
            'documents.*.number.max' => 'Document number cannot exceed 255 characters.',
            'documents.*.file.mimes' => 'Only JPG, JPEG, PNG and PDF files are allowed.',
            'documents.*.file.max' => 'File size must be less than 2MB.'
        ];

        $validator = Validator::make($r->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            // Store documents in session
            if ($r->has('documents')) {
                $documentsData = [];
                foreach ($r->documents as $document) {
                    $docData = [
                        'type' => $document['type'] ?? null,
                        'number' => $document['number'] ?? null,
                    ];

                    if (isset($document['file']) && $document['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $file = $document['file'];
                        $fileName = 'doc-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $path = Storage::disk('public')->putFileAs('company/documents', $file, $fileName);
                        $docData['file_path'] = $path;
                    }

                    $documentsData[] = $docData;
                }
                $r->session()->put('document_info', $documentsData);
            }

            // Prepare payload with just the request object
            $payload = ['r' => $r];

            LogHelper::logSuccess(
                'success',
                'Document information validated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            // Directly call StoreResponse with the payload
            return new StoreResponse($payload);
        } catch (\Exception $e) {
            LogHelper::logError(
                'exception',
                'An error occurred while processing document information.',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'An error occurred while processing documents. Please try again.',
            ]);
        }
    }

    //company details excel download
    public function excel_download(Request $r)
    {
        try {
            $company_key = $r->company_key ?? "";
            $company_name = $r->company_name ?? "";
            $phone = $r->phone ?? "";
            $email = $r->email ?? "";
            $search = "";
            $limit = $r->length !== null ? (int)$r->length : 0;
            $offset = $r->start !== null ? (int)$r->start : 0;

            $filteredData = Company::filterCompany($search, $limit, $offset, $company_key, $company_name, $email, $phone);
            $records = $filteredData['result'];

            if ($records->isEmpty()) {
                return response()->json(["message" => "No data available for download."], 204);
            }

            $csvData = [];
            foreach ($records as $row) {
                $formattedDate = "\t" . Carbon::parse($row->created_at)->format('d-m-Y');
                $status = $row->is_active ? 'Active' : 'In-Active';

                // Fix for long numbers (company_key, reg_number, GST, etc.)
                $companyKey = $row->company_key ? "\t" . $row->company_key : "";
                $regNumber = $row->reg_number ? "\t" . $row->reg_number : "";
                $gstVatNumber = $row->gst_vat_number ? "\t" . $row->gst_vat_number : "";

                // Fix for phone numbers (prevents Excel from converting to scientific notation)
                $phoneNumber = $row->phone ? "\t" . $row->phone : "";

                // Fix for postal codes (prevents removal of leading zeros)
                $officePostalCode = $row->office_address_postal_code ? "\t" . $row->office_address_postal_code : "";
                $billingPostalCode = $row->billing_address_postal_code ? "\t" . $row->billing_address_postal_code : "";

                $csvData[] = [
                    'Company Key' => $companyKey,
                    'Company Name' => $row->company_name ?? "",
                    'Company Type' => $row->company_type ?? "",
                    'Description' => $row->description ?? "",
                    'Industry Type' => $row->industry_type ?? "",
                    'Registration Number' => $regNumber,
                    'GST/VAT Number' => $gstVatNumber,
                    'Email' => $row->email ?? "",
                    'Phone' => $phoneNumber, // Fixed phone number export
                    'Website' => $row->website ?? "",
                    'Office Address Line One' => $row->office_address_line_one ?? "",
                    'Office Address Line Two' => $row->office_address_line_two ?? "",
                    'Office Address City' => $row->office_address_city ?? "",
                    'Office Address Province/State' => $row->office_address_province_state ?? "",
                    'Office Address Country Code' => $row->office_address_country_code ?? "",
                    'Office Address Postal Code' => $officePostalCode, // Fixed postal code export
                    'Is Billing Address Same' => $row->is_billing_address_same ? 'Yes' : 'No',
                    'Billing Address Line One' => $row->billing_address_line_one ?? "",
                    'Billing Address Line Two' => $row->billing_address_line_two ?? "",
                    'Billing Address City' => $row->billing_address_city ?? "",
                    'Billing Address Province/State' => $row->billing_address_province_state ?? "",
                    'Billing Address Country Code' => $row->billing_address_country_code ?? "",
                    'Billing Address Postal Code' => $billingPostalCode, // Fixed postal code export
                    'Status' => $status,
                    'Created At' => $formattedDate,
                ];
            }

            $csvFileName = 'Companies_' . date('d-m-Y') . '.csv';
            $csvFile = fopen('php://temp', 'w+');
            fputcsv($csvFile, array_keys($csvData[0])); // headers

            foreach ($csvData as $row) {
                fputcsv($csvFile, $row);
            }

            rewind($csvFile);
            $csvContent = stream_get_contents($csvFile);
            fclose($csvFile);

            $headers = [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$csvFileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0",
            ];

            return response()->make($csvContent, 200, $headers);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while downloading the company list',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                "message" => "An error occurred while generating the CSV file.",
            ], 500);
        }
    }

    //pdf download
    public function pdf_download(Request $r)
    {
        try {
            $company_key = $r->company_key ?? "";
            $company_name = $r->company_name ?? "";
            $phone = $r->phone ?? "";
            $email = $r->email ?? "";
            $search = "";
            $limit = $r->length !== null ? (int)$r->length : 0;
            $offset = $r->start !== null ? (int)$r->start : 0;

            $filteredData = Company::filterCompany($search, $limit, $offset, $company_key, $company_name, $email, $phone);
            $records = $filteredData['result'];

            $htmlContent = '<style>
					table {
						width: 100%;
						border-collapse: collapse;
					}
					table, th, td {
						border: 1px solid black;
						padding: 4px;
						font-size: 10px;
						text-align: left;
					}
					.badge-soft-success {
						color: #28a745;
						font-weight: bold;
					}
					.badge-soft-danger {
						color: #dc3545;
						font-weight: bold;
					}
				</style>';

            $htmlContent .= '<table>';
            $htmlContent .= '<thead>
								<tr>
									<th>Company Key</th>
									<th>Company Name</th>
									<th>Company Type</th>
									<th>Description</th>
									<th>Industry Type</th>
									<th>Registration Number</th>
									<th>GST/VAT Number</th>
									<th>Email</th>
									<th>Phone</th>
									<th>Website</th>

									<th>Office Address Line One</th>
									<th>Office Address Line Two</th>
									<th>Office Address City</th>
									<th>Office Address Province/State</th>
									<th>Office Address Country Code</th>
									<th>Office Address Postal Code</th>
									<th>Is Billing Address Same</th>
									<th>Billing Address Line One</th>
									<th>Billing Address Line Two</th>
									<th>Billing Address City</th>
									<th>Billing Address Province/State</th>
									<th>Billing Address Country Code</th>
									<th>Billing Address Postal Code</th>
									<th>Status</th>
									<th>Created At</th>
								</tr>
							</thead>';
            $htmlContent .= '<tbody>';

            foreach ($records as $row) {
                $createdDate = "\t" . Carbon::parse($row->created_at)->format('d-m-Y');
                $status = $row->is_active
                    ? '<span class="badge-soft-success">Active</span>'
                    : '<span class="badge-soft-danger">In-Active</span>';

                $htmlContent .= '<tr>';
                $htmlContent .= '<td>' . ($row->company_key ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->company_name ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->company_type ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->description ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->industry_type ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->reg_number ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->gst_vat_number ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->email ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->phone ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->website ?? "") . '</td>';
                //$htmlContent .= '<td>' . ($row->primary_contact_name ?? "") . '</td>';
                //$htmlContent .= '<td>' . ($row->primary_contact_email ?? "") . '</td>';
                //$htmlContent .= '<td>' . ($row->primary_contact_number ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->office_address_line_one ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->office_address_line_two ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->office_address_city ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->office_address_province_state ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->office_address_country_code ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->office_address_postal_code ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->is_billing_address_same ? 'Yes' : 'No') . '</td>';
                $htmlContent .= '<td>' . ($row->billing_address_line_one ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->billing_address_line_two ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->billing_address_city ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->billing_address_province_state ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->billing_address_country_code ?? "") . '</td>';
                $htmlContent .= '<td>' . ($row->billing_address_postal_code ?? "") . '</td>';
                $htmlContent .= '<td>' . $status . '</td>';
                $htmlContent .= '<td>' . $createdDate . '</td>';
                $htmlContent .= '</tr>';
            }

            $htmlContent .= '</tbody></table>';

            $pdf = PDF::loadHTML($htmlContent)
                ->setPaper('a2', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);

            return $pdf->download('Companies_' . date('d-m-Y') . '.pdf');
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while downloading the company PDF list',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                "message" => "An error occurred while generating the PDF file.",
            ], 500);
        }
    }

    /**
     * Display the company edit form with pre-populated data.
     *
     * This method retrieves a company by ID along with all related data needed for editing,
     * including country information, documents, and social media settings. It handles various
     * edge cases and transforms the data for proper display in the edit form.
     *
     * @param mixed $companyId The ID of the company to edit (default empty string)
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * @throws \Exception If any unexpected error occurs during processing
     */
    public function edit($companyId = "")
    {
        try {
            if (empty($companyId)) {
                return redirect()->route('company.index')->with('error', 'Invalid Company ID.');
            }

            $company = Company::with([
                'officeCountry',
                'billingCountry',
                'companyCountry',
                'companyDocument'
            ])->where('id', $companyId)
                ->whereNull('deleted_at')->first();

            if (!$company) {
                return redirect()->route('company.index')
                    ->with('error', 'Company not found or inactive.');
            }

            // Transform documents collection
            $documents = $company->companyDocument->map(function ($doc) {
                $fileExists = false;
                if (!empty($doc->document_file)) {
                    try {
                        $fileExists = Storage::exists($doc->document_file);
                    } catch (\Exception $e) {
                        // Handle exception if needed
                        $fileExists = false;
                    }
                }

                return [
                    'id' => $doc->id,
                    'type' => $doc->document_type,
                    'number' => $doc->document_number,
                    'file_path' => $doc->document_file,
                    'is_existing' => $fileExists,
                    'index' => $doc->id
                ];
            })->toArray();

            // If no documents, initialize with empty array
            if (empty($documents)) {
                $documents = [[
                    'id' => '',
                    'type' => '',
                    'number' => '',
                    'file_path' => null,
                    'is_existing' => false,
                    'index' => 0
                ]];
            }

            // Other data
            $socialMediaSettings = $company->socialMediaSettings()
                ->with('socialMediaApp')
                ->get()
                ->sortByDesc(function ($setting) {
                    return $setting->socialMediaApp->id ?? "";
                })
                ->values();
            $countries = Country::where('is_active', 1)->get();
            $socialApps = SocialMediaApp::where('is_active', 1)
                ->orderBy('id', 'DESC')  // <-- Ensure DESC order
                ->get();

            return view('main.company.edit', compact(
                'company',
                'socialMediaSettings',
                'countries',
                'companyId',
                'socialApps',
                'documents'
            ));
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'Error editing company',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Update basic company information with validation.
     *
     * This method handles the validation and temporary storage of updated basic company information.
     * It includes conditional validation for the company logo (required only if no existing logo),
     * phone number validation using libphonenumber, and comprehensive field validation.
     * Validated data is stored in session for final update processing.
     *
     * @param \Illuminate\Http\Request $r The incoming request with updated company data
     * @return \Illuminate\Http\JsonResponse JSON response with validation results or success message
     */
    public function update_basic_info(Request $r)
    {

        $company_id = $r->company_id;
        $companyLogoRule = ($r->existing_company_logo ? 'nullable' : 'nullable') . '|image|mimes:jpg,jpeg,png|max:2048|dimensions:ratio=1/1';
       // $companyLogoRule = ($r->existing_company_logo ? 'nullable' : 'nullable') . '|image|mimes:jpg,jpeg,png|dimensions:width=512,height=512';
        $rules = [
            'company_code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[A-Z]+$/',
                Rule::unique('companies', 'company_code')->whereNull('deleted_at')->ignore($company_id),
            ],
            'company_name' => 'required|string|max:100',
            'trade_name' => 'nullable|string|max:100',
            'company_country_code' => 'required',
            'legal_type' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',

            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                Rule::unique('companies', 'email')->whereNull('deleted_at')->ignore($company_id),
            ],
            'phone' => [
                'nullable',
                function ($attribute, $value, $fail) use ($r) {
                    if (empty($value)) return; // Skip if phone is empty

                    $phoneUtil = PhoneNumberUtil::getInstance();
                    try {
                        $countryCode = $r->input('phone_country');


                        $companyId = $r->company_id;

                        // Parse the phone number
                        $numberProto = $phoneUtil->parse($value, $countryCode);

                        // Validate the number
                        if (!$phoneUtil->isValidNumber($numberProto)) {
                            $fail('Invalid phone number for ' . strtoupper($countryCode));
                        }

                        // Format to E164 for uniqueness check
                        $formattedNumber = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);

                        // Check uniqueness using formatted number
                        $exists = DB::table('companies')
                            ->where('phone', $formattedNumber)
                            ->where('phone_country', $countryCode)
                            ->whereNull('deleted_at')
                            ->where("id", "!=", $companyId)
                            ->exists();

                        if ($exists) {
                            $fail('This phone number already exists.');
                        }
                    } catch (NumberParseException $e) {
                        $fail('Invalid phone number format for selected country');
                    }
                }
            ],
            'website' => 'nullable|url|max:255',
            'reg_number' => 'nullable|string|max:50',
            'gst_number' => [
                'nullable',
                'regex:/^(?:\\d{2}[A-Z]{5}\\d{4}[A-Z]{1}[A-Z\\d]{1}Z[A-Z\\d]{1}|[A-Z]{0,2}\\d{9,12})$/i'
            ],
            'account_status' => 'nullable|in:active,suspended',
            'company_logo' => $companyLogoRule,
            'is_active' => 'nullable|boolean',
            //'password' => 'nullable|min:6|max:20|regex:/^\S+$/',
            //'password_confirmation' => 'nullable|same:password|regex:/^\S+$/',
        ];

        $messages = [
            'company_code.required' => 'Company Code is required.',
            'company_code.size' => 'Company Code must be 6 characters.',
            'company_code.regex' => 'Company Code must be uppercase alphanumeric.',
            'company_code.unique' => 'This Company Code is already taken.',

            'company_name.required' => 'The company name is required.',
            'company_name.string' => 'The company name must be a string.',
            'company_name.max' => 'The company name must not exceed 100 characters.',

            'trade_name.string' => 'The trade name must be a string.',
            'trade_name.max' => 'The trade name must not exceed 100 characters.',

            'company_country_code.required' => 'The country code is required.',

            'legal_type.required' => 'The legal type is required.',
            'legal_type.string' => 'The legal type must be a string.',
            'legal_type.max' => 'The legal type must not exceed 100 characters.',

            'description.string' => 'The description must be a string.',
            'description.max' => 'The description must not exceed 255 characters.',

            'email.required' => "Email is required.",
            'email.email' => 'Enter a valid email address.',
            'email.regex' => 'The email format is not valid.',
            'email.unique' => 'The email is already in use.',

            'phone.numeric' => 'Phone number must be numeric.',
            'phone.digits_between' => 'Enter valid phone number (10-15 digits).',
            'phone.unique' => 'The phone number is already registered.',

            'website.url' => 'Website must be a valid URL.',
            'website.max' => 'The website must not exceed 255 characters.',

            'reg_number.string' => 'Registration number / Trade license number must be a string.',
            'reg_number.max' => 'Registration number / Trade license number must not exceed 50 characters.',

            'gst_number.regex' => 'Please enter a valid GST / VAT number format.',

            'account_status.in' => 'Invalid account status selected.',

            //'company_logo.required' => 'Company logo is required.',
            'company_logo.image' => 'The file must be an image.',
            'company_logo.mimes' => 'Logo must be a file of type: jpg, jpeg, png.',

            'company_logo.dimensions' => 'Logo must be square (1:1 aspect ratio).',
            'company_logo.max' => 'Logo must not be larger than 2MB.',

            'is_active.boolean' => 'The active status must be true or false.',

            //'password.min' => 'The password must be at least :min characters.',
            // 'password.max' => 'The password must not exceed :max characters.',
            //'password.regex' => 'Enter a valid password (no spaces allowed).',

            // 'password_confirmation.same' => 'Password does not match with confirm password.',
            //'password_confirmation.regex' => 'Enter a valid confirm password (no spaces allowed).',
        ];
        $validator = Validator::make($r->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            // Handle logo upload
            $path = null;

            if ($r->hasFile('company_logo')) {
                $file = $r->file('company_logo');
                $path = Storage::disk('public')->putFileAs('company/logo', $file, "company-logo-" . time() . "." . $file->getClientOriginalExtension());
            }

            $data = [
                'company_id' => $company_id,
                'company_code' => $r->company_code,
                'company_key'=> $r->company_code,
                'company_name' => $r->company_name,
                'legal_type' => $r->legal_type,
                'trade_name' => $r->trade_name,
                'company_country_code' => $r->company_country_code,
                'description' => $r->description,
                'email' => $r->email,
                'phone' => $r->phone,
                'phone_country' => $r->phone_country,
                'website' => $r->website,
                'reg_number' => $r->reg_number,
                'gst_number' => $r->gst_number,
                'password' => $r->password,
                'company_logo' => $path,
                'is_active' => $r->has('is_active') ? 1 : 0,
                'is_verified' => $r->has('is_verified') ? 1 : 0,
            ];

            if (!empty($r->password) && !empty($r->password_confirmation)) {
                $data["password"] = $r->password;
            }

            // Store in session
            $r->session()->put('basic_info', $data);

            // Log success
            LogHelper::logSuccess(
                'success',
                'Company basic info validated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 200,
                'msg' => "Company information validated successfully.",
                'data' => $data
            ]);
        } catch (\Exception $e) {
            // Log error
            LogHelper::logError(
                'exception',
                'An error occurred while validate company basic info.',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'An error occurred while update company info. Please try again later.',
            ]);
        }
    }

    /**
     * Validate and update company address information.
     *
     * This method handles the validation and temporary storage of both office and billing
     * address information for a company. It performs comprehensive validation on all address
     * fields and stores the validated data in session for final processing.
     *
     * @param \Illuminate\Http\Request $r The incoming request with address data
     * @return \Illuminate\Http\JsonResponse JSON response with validation results or success message
     */
    public function update_address_info(Request $r)
    {
        $rules = [
            'office_address_line_one' => 'required|string|max:255',
            'office_address_line_two' => 'nullable|string|max:255',
            'office_address_city' => 'required|string|max:100',
            'office_address_province_state' => 'required|string|max:100',
            'office_address_country_code' => 'required|string',
            'office_address_postal_code' => 'required|string|max:10',

            'billing_address_line_one' => 'required|string|max:255',
            'billing_address_line_two' => 'nullable|string|max:255',
            'billing_address_city' => 'required|string|max:100',
            'billing_address_province_state' => 'required|string|max:100',
            'billing_address_country_code' => 'required|string',
            'billing_address_postal_code' => 'required|string|max:10',
        ];

        $messages = [
            // Office Address - Required
            'office_address_line_one.required' => 'Office Address Line 1 is required.',
            'office_address_city.required' => 'Office City is required.',
            'office_address_province_state.required' => 'Office State/Province is required.',
            'office_address_country_code.required' => 'Office Country is required.',
            'office_address_postal_code.required' => 'Office Postal Code is required.',

            // Office Address - Max Length
            'office_address_line_one.max' => 'Office Address Line 1 must not exceed 255 characters.',
            'office_address_line_two.max' => 'Office Address Line 2 must not exceed 255 characters.',
            'office_address_city.max' => 'Office City must not exceed 100 characters.',
            'office_address_province_state.max' => 'Office State/Province must not exceed 100 characters.',
            'office_address_postal_code.max' => 'Office Postal Code must not exceed 10 characters.',

            // Office Address - Type
            'office_address_line_one.string' => 'Office Address Line 1 must be a valid string.',
            'office_address_line_two.string' => 'Office Address Line 2 must be a valid string.',
            'office_address_city.string' => 'Office City must be a valid string.',
            'office_address_province_state.string' => 'Office State/Province must be a valid string.',
            'office_address_country_code.string' => 'Office Country must be a valid string.',
            'office_address_postal_code.string' => 'Office Postal Code must be a valid string.',

            // Billing Address - Required
            'billing_address_line_one.required' => 'Billing Address Line 1 is required.',
            'billing_address_city.required' => 'Billing City is required.',
            'billing_address_province_state.required' => 'Billing State/Province is required.',
            'billing_address_country_code.required' => 'Billing Country is required.',
            'billing_address_postal_code.required' => 'Billing Postal Code is required.',

            // Billing Address - Max Length
            'billing_address_line_one.max' => 'Billing Address Line 1 must not exceed 255 characters.',
            'billing_address_line_two.max' => 'Billing Address Line 2 must not exceed 255 characters.',
            'billing_address_city.max' => 'Billing City must not exceed 100 characters.',
            'billing_address_province_state.max' => 'Billing State/Province must not exceed 100 characters.',
            'billing_address_postal_code.max' => 'Billing Postal Code must not exceed 10 characters.',

            // Billing Address - Type
            'billing_address_line_one.string' => 'Billing Address Line 1 must be a valid string.',
            'billing_address_line_two.string' => 'Billing Address Line 2 must be a valid string.',
            'billing_address_city.string' => 'Billing City must be a valid string.',
            'billing_address_province_state.string' => 'Billing State/Province must be a valid string.',
            'billing_address_country_code.string' => 'Billing Country must be a valid string.',
            'billing_address_postal_code.string' => 'Billing Postal Code must be a valid string.',
        ];

        $validator = Validator::make($r->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            $data = [
                'office_address_line_one' => $r->office_address_line_one,
                'office_address_line_two' => $r->office_address_line_two,
                'office_address_city' => $r->office_address_city,
                'office_address_province_state' => $r->office_address_province_state,
                'office_address_country_code' => $r->office_address_country_code,
                'office_address_postal_code' => $r->office_address_postal_code,

                'billing_address_line_one' => $r->billing_address_line_one,
                'billing_address_line_two' => $r->billing_address_line_two,
                'billing_address_city' => $r->billing_address_city,
                'billing_address_province_state' => $r->billing_address_province_state,
                'billing_address_country_code' => $r->billing_address_country_code,
                'billing_address_postal_code' => $r->billing_address_postal_code,

                'is_billing_address_same' => $r->has('is_billing_address_same') ? 1 : 0,
            ];

            $r->session()->put('address_info', $data);

            // Log success
            LogHelper::logSuccess(
                'success',
                'Address information validated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 200,
                'msg' => 'Address information validated successfully.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            // Log error
            LogHelper::logError(
                'exception',
                'An error occurred while validate address info.',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'An error occurred while update address info. Please try again.',
            ]);
        }
    }

    /**
     * Update company social media platform preferences.
     *
     * This method validates and stores the company's enabled social media platforms.
     * It ensures at least one platform is selected and stores only the enabled platforms
     * in session for later processing.
     *
     * @param \Illuminate\Http\Request $r The incoming request with social media selections
     * @return \Illuminate\Http\JsonResponse JSON response with validation results or success message
     */
    public function update_social_info(Request $r)
    {
        $rules = [
            'social_apps' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // Check if at least one app is enabled
                    $atLeastOneEnabled = collect($value)->contains('enabled', 1);

                    if (!$atLeastOneEnabled) {
                        $fail('Please enable at least one social media platform.');
                    }
                }
            ],
            'social_apps.*.enabled' => 'nullable|in:1'
        ];

        $messages = [
            'social_apps.required' => 'Please select at least one social media platform.',
            'social_apps.*.enabled.in' => 'Invalid value for the checkbox.'
        ];

        $validator = Validator::make($r->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            // Store only enabled apps in session
            $socialData = [];
            foreach ($r->social_apps as $appId => $appData) {
                if (!empty($appData['enabled'])) {
                    $socialData[$appId] = ['enabled' => 1];
                }
            }

            $r->session()->put('social_info', $socialData);

            LogHelper::logSuccess(
                'success',
                'Social media preferences updated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 200,
                'msg' => 'Social media preferences updated.',
                'data' => $socialData
            ]);
        } catch (\Exception $e) {
            LogHelper::logError(
                'exception',
                'Failed to update social media preferences.',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'Failed to update social media preferences. Please try again.',
            ]);
        }
    }


    /**
     * Update company document information.
     *
     * This method handles the validation and temporary storage of company documents,
     * including file uploads and document metadata. It processes both new uploads and
     * existing files, and stores the processed data in session for final update.
     *
     * @param \Illuminate\Http\Request $r The incoming request with document data
     * @return UpdateResponse|\Illuminate\Http\JsonResponse Returns UpdateResponse on success or JSON error response
     */
    public function update_document_info(Request $r)
    {
        $rules = [
            'documents' => [
                'required',
                'array',
                function ($attribute, $value, $fail) use ($r) {
                    // Check if at least one document is uploaded OR already exists
                    $hasDocument = collect($r->documents ?? [])->contains(function ($doc) {
                        return (
                            (isset($doc['file']) && $doc['file'] instanceof \Illuminate\Http\UploadedFile)
                            || !empty($doc['existing_file'])
                        );
                    });

                    if (!$hasDocument) {
                        $fail('At least one document is required.');
                    }
                },
            ],
            'documents.*.id' => 'nullable',
            'documents.*.type' => 'nullable|string|max:255',
            'documents.*.number' => 'nullable|string|max:255',
            'documents.*.file' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048'
            ],
            'documents.*.existing_file' => 'nullable|string'
        ];

        $messages = [
            'documents.*.type.string' => 'Document type must be text.',
            'documents.*.type.max' => 'Document type cannot exceed 255 characters.',
            'documents.*.number.string' => 'Document number must be text.',
            'documents.*.number.max' => 'Document number cannot exceed 255 characters.',
            'documents.*.file.mimes' => 'Only JPG, JPEG, PNG and PDF files are allowed.',
            'documents.*.file.max' => 'File size must be less than 2MB.'
        ];

        $validator = Validator::make($r->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            $documentsData = [];

            if ($r->has('documents')) {
                foreach ($r->documents as $document) {
                    $docData = [
                        'id' => $document['id'] ?? null,
                        'type' => $document['type'] ?? null,
                        'number' => $document['number'] ?? null,
                        'file_path' => $document['existing_file'] ?? null
                    ];

                    if (isset($document['file']) && $document['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $file = $document['file'];
                        $fileName = 'doc-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $path = Storage::disk('public')->putFileAs('company/documents', $file, $fileName);
                        $docData['file_path'] = $path;

                        // Delete old file if exists
                        if (!empty($document['existing_file'])) {
                            Storage::disk('public')->delete($document['existing_file']);
                        }
                    }

                    $documentsData[] = $docData;
                }
            }

            $r->session()->put('document_info', $documentsData);
            // Prepare payload with just the request object
            $payload = ['r' => $r];

            return new UpdateResponse($payload);
        } catch (\Exception $e) {
            LogHelper::logError(
                'exception',
                'Document update failed',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'Failed to update documents. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete a company and its associated social media settings.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            // Find the company
            $company = Company::find($id);

            if (!$company) {
                // Log error
                LogHelper::logError(
                    'not_found',
                    'Company not found.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    __FILE__,
                    $id
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Company not found.'
                ]);
            }

            DB::beginTransaction();

            // Soft delete the company
            $company->is_active = 0;
            $company->deleted_at = now();
            $company->save();

            // Also soft delete all related social media settings
            CompanySocialMediaSetting::where('company_id', $company->id)
                ->update([
                    'is_active' => 0,
                    'deleted_at' => now()
                ]);

            CompanyDocument::where('company_id', $company->id)
                ->update([
                    'is_active' => 0,
                    'deleted_at' => now()
                ]);

            DB::commit();

            // Log success
            LogHelper::logSuccess(
                'success',
                'Company and its social media settings deleted successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                $id
            );

            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            DB::rollBack();

            LogHelper::logError(
                'exception',
                'Error occurred while deleting company.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                $id
            );

            return response()->json([
                'message' => 'An error occurred while deleting the company.'
            ], 500);
        }
    }


    /**
     * Show company by ID
     *
     * @param mixed $companyId
     */
    public function show($companyId = "")
    {
        try {
            // Check if companyId is valid
            if (empty($companyId)) {
                return redirect()->route('company.index')->with('error', 'Invalid Company ID.');
            }

            // Fetch the company with basic validation
            $company = Company::with([
                'officeCountry',
                'billingCountry',
                'companyDocument',
                'subscriptionPurchases'
            ])
                ->where('id', $companyId)
                ->whereNull('deleted_at') // No need for table name if querying the main model
                ->first();

            //dd($company );

            if (!$company) {
                return redirect()->route('company.index')
                    ->with('error', 'Company not found or inactive.');
            }

            // Transform documents collection
            $documents = $company->companyDocument->map(function ($doc) {
                $fileExists = false;
                if (!empty($doc->document_file)) {
                    try {
                        $fileExists = Storage::exists($doc->document_file);
                    } catch (\Exception $e) {
                        // Handle exception if needed
                        $fileExists = false;
                    }
                }

                return [
                    'id' => $doc->id,
                    'type' => $doc->document_type,
                    'number' => $doc->document_number,
                    'file_path' => $doc->document_file,
                    'is_existing' => $fileExists,
                    'index' => $doc->id
                ];
            })->toArray();

            // If no documents, initialize with empty array
            if (empty($documents)) {
                $documents = [[
                    'id' => '',
                    'type' => '',
                    'number' => '',
                    'file_path' => null,
                    'is_existing' => false,
                    'index' => 0
                ]];
            }


            // Fetch social media settings (with app details)
            $socialMediaSettings = $company->socialMediaSettings()->with('socialMediaApp')->get();

            // Fetch common dropdowns (example: country list if needed)
            $countries = Country::where('is_active', 1)->get();
            $socialApps = SocialMediaApp::where('is_active', 1)->get();
            // Render the view
            return view('main.company.show', compact(
                'company',
                'socialMediaSettings',
                'countries',
                'companyId',
                'socialApps',
                'documents'
            ));
        } catch (\Exception $ex) {

            LogHelper::logError(
                'exception',
                'An error occurred while editing the company.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Remove the specified company logo from storage
     */
    public function delete_company_logo($id)
    {
        try {
            // Find the company by ID
            $company = Company::find($id);

            if ($company && $company->company_logo) {
                // Check if the logo file exists and delete it
                if (Storage::disk('public')->exists($company->company_logo)) {
                    Storage::disk('public')->delete($company->company_logo);
                }

                // Clear the company_logo field
                $company->company_logo = null;
                $company->save();

                LogHelper::logSuccess(
                    'Company logo deleted successfully',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    __FILE__,
                    $id
                );

                return response()->json(['success' => true, 'message' => 'Company logo deleted successfully.']);
            }

            LogHelper::logError(
                'not_found',
                'Company or logo not found',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                $id
            );

            return response()->json(['success' => false, 'message' => 'Company logo not found.'], 404);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                $id
            );

            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the company logo'], 500);
        }
    }


    //document Delete
    public function document_delete(string $id)
    {
        try {
            // Find the company document
            $companyDoc = CompanyDocument::find($id);

            if (!$companyDoc) {
                // Log error
                LogHelper::logError(
                    'not_found',
                    'Company document not found.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    __FILE__,
                    $id
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Company not found.'
                ]);
            }

            DB::beginTransaction();

            // Soft delete the company
            $companyDoc->is_active = 0;
            $companyDoc->deleted_at = now();
            $companyDoc->save();

            DB::commit();

            // Log success
            LogHelper::logSuccess(
                'success',
                'Company Document deleted successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                $id
            );

            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                $id
            );

            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the company document'], 500);
        }
    }


    protected function social_information($companyId)
    {
        try {
            // Get only the company's social media IDs and names
            $socialMediaSettings = CompanySocialMediaSetting::where('company_id', $companyId)
                ->where('is_active', 1)
                ->with('socialMediaApp:id,app_name')
                ->get(['social_media_app_id', 'social_media_page_link'])
                ->map(function ($setting) {
                    return [
                        'id' => $setting->social_media_app_id,
                        'name' => $setting->socialMediaApp->app_name,
                        'link' => $setting->social_media_page_link
                    ];
                });

            return $socialMediaSettings->toArray();
        } catch (\Exception $e) {
            \Log::error('Failed to fetch social info for company ' . $companyId . ': ' . $e->getMessage());
            return [];
        }
    }

    //public function add_integration_credentials(Request $r, $socialId, $companyId, $type)
    public function add_integration_credentials(Request $r, $companyId)
    {
        try {
            $getSocialDetails = $this->social_information($companyId);

            // Get all active credentials for this company (without filtering by social_media_id)
            $credentials = IntegrationCredential::where("is_active", 1)
                ->where('company_id', $companyId)
                ->get()
                ->map(function ($cred) {
                    return [
                        'id' => $cred->id,
                        'type' => $cred->type,
                        'value' => $cred->value,
                        'social_media_id' => $cred->social_media_id // Add this key
                    ];
                })
                ->toArray();

            return view('main.company.integration-credentials', [
                'credentials' => $credentials, // Pass full credentials array
                'getSocialDetails' => $getSocialDetails,
                'companyId' => $companyId,
            ]);
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Error loading integration credentials',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return redirect()->back()->with('error', 'Error loading integration credentials');
        }
    }

    //store Integration credential
    public function store_integration_credentials(Request $request)
    {
        $rules = [
            'integration_credentials' => 'nullable|array',
            'integration_credentials.*.type' => 'required|string|max:100',
            'integration_credentials.*.value' => 'required|string|max:255',
            //'company_id' => 'required|exists:companies,id',
            //'social_media_id' => 'required|exists:social_media_apps,id',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'integration_credentials.*.type.required' => 'Credential type is required.',
            'integration_credentials.*.value.required' => 'Credential value is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 200);
        }

        try {
            DB::beginTransaction();

            $companyId = $request->input('company_id');
            $socialMediaId = $request->input('social_media_id');

            foreach ($request->integration_credentials as $credential) {
                $data = [
                    'company_id' => $companyId,
                    'social_media_id' => $socialMediaId,
                    'type' => $credential['type'],
                    'value' => $credential['value'],
                    'is_active' => true,
                    'updated_at' => now(),
                ];

                if (isset($credential['id']) && $credential['id'] !== "#") {
                    // Update existing credential
                    IntegrationCredential::where('id', $credential['id'])->update($data);
                } else {
                    // Create new credential
                    $data['created_at'] = now();
                    IntegrationCredential::create($data);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'msg' => 'Integration credentials saved successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            LogHelper::logError(
                'exception',
                'Failed to store integration credentials',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $request->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 500,
                'msg' => 'Failed to save integration credentials. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //delete integration credential
    public function integration_credential_delete(string $id)
    {
        try {
            // Find the integration credential

            $credential = IntegrationCredential::find($id);

            if (!$credential) {
                // Log error
                LogHelper::logError(
                    'not_found',
                    'Integration credential not found.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null,
                    ['credential_id' => $id]
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Integration credential not found.'
                ], 404);
            }

            DB::beginTransaction();

            // Soft delete the credential
            $credential->is_active = 0;
            $credential->deleted_at = now();
            $credential->save();

            DB::commit();

            // Log success
            LogHelper::logSuccess(
                'success',
                'Integration credential deleted successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null,
                ['credential_id' => $id]
            );

            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            DB::rollBack();

            LogHelper::logError(
                'exception',
                'Failed to delete integration credential',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null,
                ['credential_id' => $id]
            );

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the credential'
            ], 500);
        }
    }


    /**
     * Update company status (active/suspended)
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_status(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:active,suspended',
                'reason' => 'nullable|string|required_if:status,suspended|max:500'
            ]);

            $company = Company::find($id);
            if (!$company) {
                LogHelper::logError(
                    'not_found',
                    'Company not found',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::id(),
                    ['company_id' => $id]
                );
                return response()->json(['success' => false, 'message' => 'Company not found'], 404);
            }

            DB::beginTransaction();

            $company->account_status = $validated['status'];
            $company->reason = $validated['status'] === 'suspended'
                ? $validated['reason']
                : null;
            $company->save();

            //update admin side company status
            $adminData = [
                "status" => $validated['status']
            ];
            $this->makeSecurePostApiRequest(strtolower($company->company_unique_code) . '/api/' . env('API_VERSION') . '/company-status-update', $adminData)->throw();

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => "Company status updated to {$validated['status']}"
            ]);
        } catch (RequestException $exception) {
            DB::rollBack();
            LogHelper::logError('exception', 'API => ' . __('api.super_admin_error'), $exception->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, '');
            return response()->json(['status' => 500, 'msg' => "Failed to update company status. Please try again.", 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            LogHelper::logError(
                'exception',
                'Status update failed',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::id(),
                ['company_id' => $id]
            );
            return response()->json([
                'status' => 500,
                'msg' => 'Failed to update company status. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function setup($id)
    {
        $company = Company::where('id', $id)->first();
        if ($company) {
            return view('main.company.setup', compact('company'));
        } else {
            return redirect()->back()->with('error', "Unable to find such company details. Please check url once again");
        }
    }

    public function setupAction($id)
    {
        $company = Company::where('id', $id)->first();
        if ($company) {
            $steps = SiteSetupStep::where('company_id', $id)->orderBY('order_no', 'ASC')->get();
            $html = view('main.company.setup-actions', compact('steps', 'company'))->render();
            return response()->json(['html' => $html]);
        } else {
            return response()->json(['html' => "Unable to find the company into the records. Please check the url once again"]);
        }
    }

    //subscription update
    public function subscription_update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $rules = [
                'from_date' => 'required|date',
                'to_date'   => 'required|date|after_or_equal:from_date',
            ];

            $messages = [
                'from_date.required' => 'The start date is required.',
                'from_date.date'     => 'The start date must be a valid date.',
                'to_date.required'   => 'The end date is required.',
                'to_date.date'       => 'The end date must be a valid date.',
                'to_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ]);
            }

            $sub = SubscriptionPurchase::findOrFail($id);

            $sub->from_date = Carbon::parse($request->from_date);
            $sub->to_date = Carbon::parse($request->to_date);
            $sub->status = $request->status;
            $sub->save();

            //update admin side package details

            $adminData = [
                "package_id" => $sub->subscription_purchase_id,
                "from_date" => $request->from_date,
                "to_date" => $request->to_date,
                "status" => $request->status,
            ];

            $company = Company::where('id', $sub->company_id)->first();

            $this->makeSecurePostApiRequest(strtolower($company->company_unique_code) . '/api/' . env('API_VERSION') . '/subscription-plan-update', $adminData)->throw();

            //$this->makeSecurePostApiRequest('api/v1/subscription-plan-update', $adminData)->throw();

            DB::commit();

            LogHelper::logSuccess('success', 'Subscription updated successfully', __FUNCTION__, __FILE__, __LINE__, request()->path(), $sub->id);

            return response()->json([
                'status' => 200,
                'message' => 'Subscription updated successfully.'
            ]);
        } catch (RequestException $exception) {
            DB::rollBack();
            LogHelper::logError('exception', 'API => ' . __('api.super_admin_error'), $exception->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, '');
            return response()->json(['status' => 500, 'message' => 'Update failed', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            LogHelper::logError('exception', 'Update failed', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), $id);

            return response()->json([
                'status' => 'error',
                'message' => 'Update failed'
            ], 500);
        }
    }

    //plan add subscription plan add

    public function subscription_add(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $rules = [
                'subscription' => 'required',
            ];

            $messages = [
                'subscription.required' => 'The subscription is required.'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ]);
            }

            $subscriptionData = Subscription::where("id", $request->subscription)->first();
            if (!$subscriptionData) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid subscription selected.'
                ], 404);
            }

            //subscription package update
            $previousPlan = SubscriptionPurchase::where('company_id', $request->company_id)
                ->where('status', 'active')
                ->where('payment_status', 'paid')
                ->where('is_active', 1)
                ->first();

            $company = Company::where('id', $request->company_id)->first();

            if ($previousPlan) {
                // Update locally
                $previousPlan->update([
                    'status'    => 'inactive',
                    'is_active' => 0,
                    'to_date'   => now()
                ]);

                $adminData = [
                    "package_id" => $previousPlan->subscription_purchase_id,
                    "status" => "inactive",
                    "is_active" => 0
                ];

                $this->makeSecurePostApiRequest(strtolower($company->company_unique_code) . '/api/' . env('API_VERSION') . '/subscription-plan-update', $adminData)->throw();

                //$this->makeSecurePostApiRequest('api/v1/subscription-plan-update', $adminData)->throw();
            }


            $startDate = now();
            $toDate = $startDate->copy()->addMonths($subscriptionData->subscription_months);

            $orderId = "ORD" . (int)(microtime(true) * 1000);
            $subscription_plan = [
                "company_id"                   => $request->company_id,
                "subscription_id"              => $subscriptionData->id,
                "subscription_title"           => $subscriptionData->subscription_title,
                "subscription_months"          => $subscriptionData->subscription_months,
                "subscription_per_month_price" => $subscriptionData->subscription_per_month_price,
                "subscription_total_price"     => $subscriptionData->subscription_total_price,
                "from_date"                    => $startDate,
                "to_date"                      => $toDate,
                "discount_type"                => $subscriptionData->discount_type,
                "discount_value"               => $subscriptionData->discount_value,
                "payment_status"               => "paid",
                "payment_mode"                 => "online",
                "payment_id"                   => $orderId,
                "is_active"                    => 1,
                "status"                       => "active",
                "currency_code"                => $subscriptionData->currency_code,
            ];


            //$this->makeSecurePostApiRequest($company->company_code.'/api/v1/subscription-plan-add', $subscription_plan)->throw();
            $response = $this->makeSecurePostApiRequest(strtolower($company->company_unique_code) . '/api/' . env('API_VERSION') . '/subscription-plan-add', $subscription_plan)->throw();
            $data = $response->json();
            $subscription_purchase_id = $data['id'] ?? null;
            $subscription_plan["subscription_purchase_id"] = $subscription_purchase_id;
            $purchasePlan = SubscriptionPurchase::create($subscription_plan);

            DB::commit();
            LogHelper::logSuccess('success', 'Subscription change successfully.', __FUNCTION__, __FILE__, __LINE__, request()->path(), $subscriptionData->id);

            return response()->json([
                'status' => 200,
                'message' => 'Subscription change successfully.'
            ]);
        } catch (RequestException $exception) {
            DB::rollBack();
            LogHelper::logError('exception', 'API => ' . __('api.super_admin_error'), $exception->getMessage(), __FUNCTION__, basename(__FILE__), __LINE__, '');
            return response()->json(['status' => 500, 'message' => 'Subscription change failed', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            LogHelper::logError('exception', 'Subscription change failed', $e->getMessage(), __FUNCTION__, __FILE__, __LINE__, request()->path(), $id);

            return response()->json([
                'status' => 'error',
                'message' => 'Subscription change failed'
            ], 500);
        }
    }



    public function subscription_for_renew(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');
            $today = date('Y-m-d');
            // Get all subscription IDs already actively used
            $usedIds = SubscriptionPurchase::where('status', "active")->pluck('subscription_id');

            $result = Subscription::where('subscription_title', 'like', '%' . $search . '%')
                ->where('is_active', 1)
                ->whereDate('from_date', '<=', $today)  // from_date should be <= today
                ->whereDate('to_date', '>=', $today)
                //->whereNotIn('id', $usedIds)
                ->orderBy('id', 'DESC')
                //->limit(20)
                ->get();

            foreach ($result as $item) {
                $html[] = ['id' => $item->id, 'text' => $item->subscription_title];
            }

            return response()->json($html);
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Error while fetching subscription title list',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([]);
        }
    }


    public function subscription_update_status(Request $r, string $id)
    {
        try {

            $subscription = SubscriptionPurchase::where("id", $id)->first();

            if (!$subscription) {
                // Log error
                LogHelper::logError(
                    'not_found',
                    'Subscription Purchase not found.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    __FILE__,
                    $id
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Subscription Purchase not found.'
                ]);
            }

            DB::beginTransaction();

            // Soft delete the company
            $subscription->status = $r->status;
            $subscription->save();
            DB::commit();

            // Log success
            LogHelper::logSuccess(
                'success',
                'Subscription Purchase successfully suspend.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                $id
            );

            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            DB::rollBack();

            LogHelper::logError(
                'exception',
                'Error occurred while subscription purchase successfully suspend.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                $id
            );

            return response()->json([
                'message' => 'An error occurred while subscription purchase successfully suspend.'
            ], 500);
        }
    }

    public function subscription_plan(Request $request)
    {

        // Fetch subscription purchase by ID
        $purchase = SubscriptionPurchase::find($request->id);

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription Purchase not found.'
            ]);
        }

        // Return the subscription data as JSON
        return response()->json([
            'success' => true,
            'data' => $purchase
        ]);
    }
}
