<?php


/** --------------------------------------------------------------------------------
 * This controller manages all the users operations
 *
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Response;

// Helper
use App\Helpers\LogHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\PermissionGroup;
use Spatie\Permission\Models\Permission;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use Illuminate\Support\Facades\Session;


class UserController extends Controller
{
    //parent
    public function __construct()
    {
        //Permissions on methods
        $this->middleware('permission:User.List,admin')->only(['index']);
        $this->middleware('permission:User.Create,admin')->only(['create', 'store']);
        $this->middleware('permission:User.View,admin')->only('show');
        $this->middleware('permission:User.Edit,admin')->only(['edit', 'update']);
        $this->middleware('permission:User.Delete,admin')->only('destroy');
        $this->middleware('permission:User.Export,admin')->only(['excel_download', 'pdf_download']);
        $this->middleware('permission:User.Block,admin')->only(['block_unblock_user']);
    }

    /**
     * Display a listing of users
     *
     * This method handles the display of the user index page. It includes logging for both success
     * and error cases to help with debugging and monitoring.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        try {
            // Log successful access to the user index page
            LogHelper::logSuccess(
                'success',
                'User index page loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            // Return the user index view
            return view('main.user.index');
        } catch (\Exception $ex) {
            // Log any exceptions that occur during the process
            LogHelper::logError(
                'exception',
                'An error occurred while loading the user index page',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            // Return error response to the user with a redirect back
            return redirect()->back()->with('error', 'An error occurred while loading the user list.');
        }
    }

    /**
     * Get a paginated and filtered list of users for DataTables
     *
     * This method handles server-side processing for the users datatable, including:
     * - Pagination (limit/offset)
     * - Search filtering
     * - Role-based access control for actions
     * - Status formatting
     * - Detailed logging for success/error cases
     *
     * @param \Illuminate\Http\Request $r The request object containing DataTables parameters
     * @return \Illuminate\Http\JsonResponse JSON response formatted for DataTables
     */
    public function list(Request $r)
    {
        try {
            $limit = $r->length;
            $offset = $r->start;
            $search = $r->input('search.value') ?? "";
            $user = $r->user ?? "";
            $role = $r->role ?? "";

            $filteredData = User::filterUser($search, $limit, $offset, $user, $role);
            $total = $filteredData['totalRecords'];
            $records = $filteredData['result'];

            $data = [];
            $srno = $offset + 1;

            $canViewAction = Auth::guard('admin')->user()->canany([
                'User.Edit',
                'User.View',
                'User.Delete',
                'User.Permissions',
                'User.Block'
            ]);

            if ($records->count() > 0) {
                foreach ($records as $row) {
                    $formattedDate = \Carbon\Carbon::parse($row->created_at)->format('d-m-Y');

                    $status = $row->is_active == 1
                        ? '<div><span class="badge rounded-pill badge-soft-success">Active</span></div>'
                        : '<div><span class="badge rounded-pill badge-soft-danger">In-Active</span></div>';

                    $blockstatus = $row->is_block == 1
                        ? '<div><span class="badge rounded-pill badge-soft-success">Yes</span></div>'
                        : '<div><span class="badge rounded-pill badge-soft-danger">No</span></div>';

                    $action = '';
                    if ($canViewAction) {
                        $action .= '
							<span>
								<div class="dropdown font-sans-serif position-static">
									<button class="btn btn-link text-600 btn-sm btn-reveal" type="button" id="user-dropdown-' . $row->id . '" data-bs-toggle="dropdown" data-boundary="window"
										aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs--1"></span>
									</button>
									<div class="dropdown-menu dropdown-menu-end border py-0" aria-labelledby="user-dropdown-' . $row->id . '">
										<div class="bg-white py-2">';

                        if (Auth::guard('admin')->user()->can('User.View')) {
                            $action .= '<a class="dropdown-item" href="' . url('users/' . $row->id) . '"> <i class="fas fa-eye"></i> View</a>';
                        }
                        if (Auth::guard('admin')->user()->can('User.Edit')) {
                            $action .= '<a class="dropdown-item btn-edit" href="' . url('users/' . $row->id . '/edit') . '"> <i class="fas fa-edit"></i> Edit</a>';
                        }
                        if (Auth::guard('admin')->user()->can('User.Permissions')) {
                            $action .= '<a class="dropdown-item" href="' . url('user/' . $row->id . '/permissions') . '"> <i class="fas fa-check-double"></i> Permissions</a>';
                        }
                        if (Auth::guard('admin')->user()->can('User.Delete')) {
                            $action .= '<a class="dropdown-item btn-delete" style="cursor: pointer;" data-id="' . $row->id . '"> <i class="far fa-trash-alt"></i> Delete</a>';
                        }
                        if (Auth::guard('admin')->user()->can('User.Block')) {
                            if (empty($row->is_block)) {
                                $action .= '<a class="dropdown-item btn-block" style="cursor: pointer;" data-id="' . $row->id . '" data-val="0"> <i class="fas fa-angle-up"></i> Block</a>';
                            } else {
                                $action .= '<a class="dropdown-item btn-block" style="cursor: pointer;" data-id="' . $row->id . '" data-val="1"> <i class="fas fa-angle-down"></i> Un-Block</a>';
                            }
                        }

                        $action .= '</div></div></div></span>';
                    }

                    // Prepare row data
                    $rowData = [];

                    if ($canViewAction) {
                        $rowData[] = $action;
                    }

                    $rowData[] = $row->username;
                    $rowData[] = $row->first_name . " " . $row->last_name;
                    $rowData[] = $row->role_name;
                    $rowData[] = $row->email;
                    $rowData[] = $row->phone;
                    $rowData[] = $status;
                    $rowData[] = $blockstatus;

                    $data[] = $rowData;
                    $srno++;
                }
            }

            LogHelper::logSuccess(
                'success',
                'User list fetched successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                "draw" => intval($r->draw),
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $data
            ], 200);
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while fetching the user list',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                "message" => "An error occurred while fetching the user list",
            ], 500);
        }
    }



    /**
     * Get a filtered list of roles for dropdown selection
     *
     * This method provides a searchable list of roles (excluding role ID 1) formatted for
     * select dropdowns. It's typically used for AJAX-powered select2 dropdowns.
     * Includes comprehensive logging for both success and error cases.
     *
     * @param \Illuminate\Http\Request $r The request object containing search parameters
     * @return \Illuminate\Http\JsonResponse JSON response formatted for select2 dropdown
     */
    public function get_role(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');
            $result = Role::where('name', 'like', '%' . $search . '%')
                ->where('id', "!=", 1)
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            if ($result) {
                foreach ($result as $item) {
                    $html[] = ['id' => $item->id, 'text' => $item->name];
                }
            }

            // success log
            LogHelper::logSuccess(
                'success',
                'Role list fetched successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                \Auth::guard('admin')->user()->id ?? null
            );

            return response()->json($html);
        } catch (\Exception $ex) {
            // error log
            LogHelper::logError(
                'exception',
                'An error occurred while fetching the role dropdown list.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                \Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([]);
        }
    }


    /**
     * Get a filtered list of users for dropdown selection
     *
     * This method provides a searchable list of active users (excluding user ID 1) formatted
     * for select dropdowns. It searches both first and last names and returns results
     * in a format compatible with select2 dropdowns.
     *
     * @param \Illuminate\Http\Request $r The request object containing search parameters
     * @return \Illuminate\Http\JsonResponse JSON response containing user list formatted for dropdown
     */

    public function get_users(Request $r)
    {
        try {
            $html = [];
            $search = $r->input('search');

            $result = User::whereNull("deleted_at")->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%');
            })
                ->where('id', '!=', 1)
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();

            if ($result) {
                foreach ($result as $item) {
                    $fullName = trim($item->first_name . ' ' . $item->last_name);
                    $html[] = ['id' => $item->id, 'text' => $fullName];
                }
            }

            // success log
            LogHelper::logSuccess(
                'success',
                'User list fetched successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                \Auth::guard('admin')->user()->id ?? null
            );

            return response()->json($html);
        } catch (\Exception $ex) {
            // error log
            LogHelper::logError(
                'exception',
                'An error occurred while fetching the user dropdown list.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                \Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([]);
        }
    }


    /**
     * Display the user creation form
     *
     * This method handles the display of the user add form page. It includes:
     * - Success logging when the page loads correctly
     * - Error handling and logging if something goes wrong
     * - Appropriate response to the user in both cases
     *
     * @param \Illuminate\Http\Request $r The incoming request object
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create(Request $r)
    {
        try {
            // Log success when user add page is loaded
            LogHelper::logSuccess(
                'success',
                'User add page loaded successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return view('main.user.add');
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError(
                'exception',
                'An error occurred while loading the user add page.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while loading the user add page.');
        }
    }

    /**
     * Store a newly created user in the database
     *
     * This function handles:
     * 1. Validation of user input data
     * 2. Creation of new user record
     * 3. Avatar image upload handling
     * 4. Permission assignment
     * 5. Transaction management for data integrity
     * 6. Comprehensive logging for both success and error cases
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user data
     * @return \Illuminate\Http\RedirectResponse Redirects with status message
     */
    public function store(Request $request)
    {

        // Validate input
        $validator = Validator::make($request->all(), [
            'role' => 'required',
            'username' => [
                'required',
                'min:3',
                'max:150',
                Rule::unique('users')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                })
            ],
            'first_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
            'last_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
            'phone_number' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if (empty($value)) return; // Skip if phone is empty

                    $phoneUtil = PhoneNumberUtil::getInstance();
                    try {
                        $countryCode = $request->input('phone_country');
                        $numberProto = $phoneUtil->parse($value, $countryCode);

                        if (!$phoneUtil->isValidNumber($numberProto)) {
                            $fail('Invalid phone number');
                            //$fail('Invalid phone number for ' . $phoneUtil->getRegionCodeForCountryCode($numberProto->getCountryCode()));
                        }

                        // Check uniqueness for this country
                        $formattedNumber = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
                        $exists = DB::table('users')
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
            'email' => [
                'required',
                'email',
                'min:2',
                'max:150',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                Rule::unique('users')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                })
            ],
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
            //'password' => 'required|min:6|max:20|regex:/^\S+$/',
            //'password_confirmation' => 'required|same:password|regex:/^\S+$/',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048|dimensions:ratio=1/1',
        ], [
            'role.required' => 'Role is required.',
            'username.required' => 'Username is required.',
            'username.min' => 'Username must be at least :min characters.',
            'username.max' => 'Username cannot exceed :max characters.',
            'username.unique' => 'This username is already taken.',

            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'First name must contain only letters and spaces.',
            'first_name.min' => 'First name must be at least :min characters.',
            'first_name.max' => 'First name cannot exceed :max characters.',

            'last_name.required' => 'Last name is required.',
            'last_name.regex' => 'Last name must contain only letters and spaces.',
            'last_name.min' => 'Last name must be at least :min characters.',
            'last_name.max' => 'Last name cannot exceed :max characters.',

            'phone_number.required' => 'Phone number is required.',
            'phone_number.digits_between' => 'Phone number must be between 10 and 15 digits.',
            'phone_number.numeric' => 'Phone number must be numeric.',
            'phone_number.unique' => 'This phone number is already taken.',

            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.min' => 'Email must be at least :min characters.',
            'email.max' => 'Email cannot exceed :max characters.',
            'email.regex' => 'Please enter a valid email format.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least :min characters.',
            //'password.max' => 'Password cannot exceed :max characters.',
            //'password.regex' => 'Password cannot contain spaces.',

            'password_confirmation.required' => 'Please confirm your password.',
            'password_confirmation.same' => 'Password confirmation does not match.',
            //'password_confirmation.regex' => 'Password confirmation cannot contain spaces.',
            // 'avatar.dimensions' => 'The image must have dimensions of exactly 512×512 pixels (width × height).',
            //'avatar.max' => 'The image must not be larger than 2MB.',
            'avatar.image' => 'Uploaded file must be an image.',
            'avatar.mimes' => 'Image must be a file of type: jpeg, png, jpg.',

            'avatar.dimensions' => 'The image must be square (1:1 aspect ratio).',
            'avatar.max' => 'The image must not be larger than 2MB.',
        ]);

        if ($validator->fails()) {
            LogHelper::logError(
                'Validation failed while adding a user',
                json_encode($validator->errors()->all()),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                auth()->id() ?? null
            );
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 200);
        }
        try {
            // Start DB transaction
            DB::beginTransaction();

            $phoneUtil = PhoneNumberUtil::getInstance();
            $countryCode = $request->input('phone_country');
            $numberProto = $phoneUtil->parse($request->phone_number, $countryCode);

            $user = new User;
            $user->role_id = $request->role;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->username = $request->username;
            $user->phone = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
            $user->phone_country = $request->phone_country;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->is_active = 1;
            $user->is_block = 0;

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $imageName = 'avatar-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('avatar', $file, $imageName);
                $user->avatar = $path;
            }

            $user->save();


            //give permissions to user other than admin
            if ($request->role_id != 1) {
                $permissions = Permission::where('id', "13")->first();
                if ($permissions) {
                    $users = User::find($user->id);
                    $users->givePermissionTo($permissions);
                }
            }
            //permission ended


            DB::commit();

            LogHelper::logSuccess(
                'User added successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                $user->id
            );

            return response()->json([
                'status' => 200,
                'message' => 'User added successfully.'
            ], 200);
        } catch (\Exception $ex) {
            DB::rollback();

            LogHelper::logError(
                'An error occurred while saving the user',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                auth()->id() ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while saving the user.');
        }
    }


    /**
     * Display the user edit form
     *
     * This method handles:
     * - Retrieving user data for editing
     * - Validating user existence
     * - Comprehensive logging for both success and error cases
     * - Proper error handling and user feedback
     *
     * @param string $id The ID of the user to edit
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(string $id)
    {
        try {
            $user = User::with('role')->where('id', $id)
                ->whereNull('deleted_at')->first();

            if (!$user) {
                LogHelper::logError(
                    'error',
                    'Invalid user requested for editing.',
                    'User not found or deleted.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );
                return redirect()->back()->with('error', 'Invalid user.');
            }

            LogHelper::logSuccess(
                'success',
                'User data loaded successfully for editing.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return view('main.user.edit', compact('user'));
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while editing the user.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect()->back()->with('error', 'An error occurred while editing the user.');
        }
    }


    /**
     * Update user information
     *
     * This method handles:
     * - Validating user input data including username, names, phone, email, password and avatar
     * - Checking for unique fields while ignoring the current user's records
     * - Updating user details in the database
     * - Handling password changes conditionally
     * - Managing avatar image uploads
     * - Comprehensive logging for both success and error cases
     * - Proper error handling and user feedback
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user data
     * @param string $id The ID of the user to update
     * @return \Illuminate\Http\RedirectResponse Redirects back with status message
     */

    public function update(Request $request, string $id)
    {


        //check validation

        $validator = Validator::make($request->all(), [
            'role' => 'required',
            'username' => ['required', 'min:3', 'max:150', Rule::unique('users')->ignore($id)->whereNull('deleted_at')],
            'first_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
            'last_name' => ['required', 'regex:/^[A-Za-z\s]+$/', 'min:2', 'max:150'],
            'phone_number' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if (empty($value)) return; // Skip if phone is empty

                    $phoneUtil = PhoneNumberUtil::getInstance();
                    try {
                        $countryCode = $request->input('phone_country');

                        $numberProto = $phoneUtil->parse($value, $countryCode);
                        $user_id = $request->user_id;
                        if (!$phoneUtil->isValidNumber($numberProto)) {
                            $fail('Invalid phone number');
                            //$fail('Invalid phone number for ' . $phoneUtil->getRegionCodeForCountryCode($numberProto->getCountryCode()));
                        }

                        // Check uniqueness for this country
                        $formattedNumber = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
                        $exists = DB::table('users')
                            ->where('phone', $formattedNumber)
                            ->where('phone_country', $countryCode)
                            ->whereNull('deleted_at')
                            ->where("id", "!=", $user_id)
                            ->exists();

                        if ($exists) {
                            $fail('This phone number already exists.');
                        }
                    } catch (NumberParseException $e) {
                        $fail('Invalid phone number format for selected country');
                    }
                }
            ],
            'email' => [
                'required',
                'email',
                'min:2',
                'max:150',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                Rule::unique('users')->ignore($id)->whereNull('deleted_at')
            ],
            // 'password' => 'nullable|min:6|max:20|regex:/^\S+$/',
            //'password_confirmation' => 'nullable|same:password|regex:/^\S+$/',
            'password' => 'nullable|min:8',
            'password_confirmation' => 'nullable|same:password',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048|dimensions:ratio=1/1',
            //'avatar' => 'nullable|image|mimes:jpeg,png,jpg|dimensions:width=512,height=512',
        ], [
            'role.required' => 'Role is required.',
            'username.required' => 'Username is required.',
            'username.min' => 'Username must be at least :min characters.',
            'username.max' => 'Username cannot exceed :max characters.',
            'username.unique' => 'This username is already taken.',

            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'The first name must only contain alphabets and spaces.',
            'first_name.min' => 'The First name must be at least 2 characters long.',
            'first_name.max' => 'The First name cannot exceed 150 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.regex' => 'The last name must only contain alphabets and spaces.',
            'last_name.min' => 'The Last name must be at least 2 characters long.',
            'last_name.max' => 'The Last name cannot exceed 150 characters.',
            'phone_number.required' => 'The Phone number is required.',
            'phone_number.digits_between' => 'Phone number must be between 10 and 15 digits.',
            'phone_number.numeric' => 'Please enter a valid number.',
            'phone_number.unique' => 'The phone number has already been taken.',
            'email.required' => 'The Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'email.min' => 'The Email must be at least 2 characters long.',
            'email.max' => 'The Email cannot exceed 150 characters.',
            'password.min' => 'The password must be at least :min characters.',
            //'password.max' => 'The password must not exceed :max characters.',
            'password_confirmation.same' => 'Password does not match the confirm password.',
            //'password.regex' => 'Enter a valid password.',
            //'avatar.dimensions' => 'The image must have dimensions of exactly 512×512 pixels (width × height).',
            //'avatar.max' => 'The image must not be larger than 2MB.',
            'avatar.image' => 'The file must be an image.',
            'avatar.mimes' => 'The image must be of type: jpeg, png, jpg.',

            'avatar.dimensions' => 'The image must be square (1:1 aspect ratio).',
            'avatar.max' => 'The image must not be larger than 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 200);
        }

        try {
            //update user
            $user = User::find($id);

            if (!$user) {
                LogHelper::logError(
                    'error',
                    'Invalid user requested for update.',
                    'User not found or deleted.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );
                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid user.');
            }

            $phoneUtil = PhoneNumberUtil::getInstance();
            $countryCode = $request->input('phone_country');
            $numberProto = $phoneUtil->parse($request->phone_number, $countryCode);


            $user->role_id = $request->role;
            $user->username = $request->username;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->phone = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
            $user->phone_country = $request->phone_country;
            $user->email = $request->email;
            if ($request->password != "" && $request->password_confirmation != "") {
                $user->password = Hash::make($request->password);

                //logout session from all device

                DB::table('sessions')
                    ->where('user_id',  $user->id)
                    ->where('id', '!=', session()->getId())
                    ->delete();
            }
            $user->is_active = $request->is_active ? 1 : 0;

            // Handle the avatar image upload
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $imageName = 'avatar-' . time() . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('avatar', $file, $imageName);
                $user->avatar = $path; // Save the image name in the database
            }
            $user->update();
            //success log
            LogHelper::logSuccess(
                'success',
                'User data updated.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return response()->json([
                'status' => 200,
                'message' => 'User updated successfully.'
            ], 200);
        } catch (\Exception $ex) {
            // Log the error
            LogHelper::logError(
                'exception',
                'An error occurred while updating the user.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while update the user.');
        }
    }



    /**
     * Displays detailed view of a specific user
     *
     * Retrieves a user with their role information by ID and verifies the user exists and isn't deleted.
     * Handles cases where user isn't found and logs all viewing attempts.
     *
     * @param string $id The ID of the user to display
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Returns user detail view or error redirect
     * @throws \Exception Logs errors during user retrieval process
     */
    public function show(string $id)
    {
        try {
            $user = User::with('role')->where('id', $id)->whereNull("deleted_at")->first();

            if (!$user) {
                // Log the error
                LogHelper::logError(
                    'error',
                    'Invalid user requested for showing.',
                    'User not found or deleted.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );

                // Return error response to the user
                return redirect()->back()->with('error', 'The invalid user.');
            }
            return view('main.user.show', compact('user'));
        } catch (\Exception $ex) {

            // Log the error
            LogHelper::logError(
                'exception',
                'An error occurred while showing the user.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while view the user.');
        }
    }


    /*
	 * delete user profile image
	 */

    public function delete_avatar(Request $r, string $id)
    {
        try {
            $user = User::find($r->id);

            if (!$user) {
                // Log the error with your format
                LogHelper::logError(
                    'error',
                    'Invalid user requested for avatar deletion.',
                    'User not found or deleted.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );
                return redirect()->back()->with('error', 'The invalid user.');
            }

            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);

            // success log with your format
            LogHelper::logSuccess(
                'success',
                'User avatar deleted successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return redirect('users/' . $r->id . '/edit')->with('success', 'Avatar deleted successfully');
        } catch (\Exception $ex) {
            // Log the exception with your format
            LogHelper::logError(
                'exception',
                'An error occurred while deleting the avatar image.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return redirect()->back()->with('error', 'An error occurred while deleting avatar.');
        }
    }



    /**
     * Deletes a user's profile avatar image
     *
     * Handles avatar deletion process including:
     * - Validates user exists
     * - Deletes file from storage
     * - Updates user record
     * - Logs all actions and errors
     *
     * @param Request $r Incoming HTTP request
     * @param string $id ID of user whose avatar to delete
     * @return \Illuminate\Http\RedirectResponse Redirects back with status message
     * @throws \Exception Logs storage or database errors
     */
    public function block_unblock_user(String $id)
    {
        try {
            // Find the user by ID
            $user = User::find($id);

            // Check if the user exists
            if (!$user) {
                // Log the error with your format
                LogHelper::logError(
                    'error',
                    'Invalid user requested for block/unblock.',
                    'User not found or deleted.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );

                //return error response
                return response()->json([
                    'success' => false,
                    'error' => 'User not found.'
                ]);
            }

            // Toggle block/unblock status
            if ($user->is_block == 0) {
                // Block the user
                $user->is_block = 1;
            } else {
                // Unblock the user
                $user->is_block = 0;
            }

            // Save user status
            if ($user->save()) {
                // success log with your format
                LogHelper::logSuccess(
                    'success',
                    'User block/unblock action successful.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );

                //return response to js function
                return response()->json(['success' => true]);
            } else {
                // Log the error with your format
                LogHelper::logError(
                    'error',
                    'Failed to save user block/unblock status.',
                    'Database save operation failed.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );

                //Return response to user
                return response()->json(['success' => false, 'error' => 'Failed to update user status.']);
            }
        } catch (\Exception $ex) {
            // Log the exception with your format
            LogHelper::logError(
                'exception',
                'An error occurred while block/unblock user.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            //Return response to user
            return redirect()->back()->with('error', 'An error occurred while block/unblock user.');
        }
    }


    /**
     * Soft deletes a user record
     *
     * Handles user deletion by setting deleted_at timestamp (soft delete).
     * Validates user existence, logs all actions, and returns JSON responses.
     *
     * @param string $id ID of the user to delete
     * @return \Illuminate\Http\JsonResponse JSON response with success/error status
     * @throws \Exception Logs database errors during deletion process
     */
    public function destroy(string $id)
    {
        try {
            // Find the user by ID
            $user = User::find($id);

            // Check if the user exists
            if (!$user) {
                // Log the error with your format
                LogHelper::logError(
                    'error',
                    'Invalid user requested for deletion.',
                    'User not found or already deleted.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );

                // Return error response
                return response()->json([
                    'success' => false,
                    'error' => 'User not found.'
                ]);
            }

            // Soft delete: set is_delete flag and deleted_at timestamp
            $user->deleted_at = now();  // set current timestamp
            $user->save();

            // Log success
            LogHelper::logSuccess(
                'success',
                'User deleted (soft delete) successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            // Return success response
            return response()->json(['success' => true]);
        } catch (\Exception $ex) {
            // Log exception with your format
            LogHelper::logError(
                'exception',
                'An error occurred while deleting the user.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            // Return error response
            return response()->json([
                'message' => 'An error occurred while deleting the user.'
            ], 500);
        }
    }


    // Excel downloading
    public function excel_download(Request $r)
    {
        try {
            $user = $r->user ?? "";
            $role = $r->role ?? "";
            $search = "";
            $limit = $r->length ? (int)$r->length : null;
            $offset = $r->start ? (int)$r->start : 0;

            $filteredData = User::filterUser($search, $limit, $offset, $user, $role);
            $records = $filteredData['result'];

            if (empty($records)) {
                LogHelper::logError(
                    'error',
                    'No user data found for Excel download.',
                    'No records matched the filter criteria.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );
                return response()->json(["message" => "No data available for download."], 204);
            }

            $csvData = [];
            foreach ($records as $row) {
                $carbonDate = Carbon::parse($row->created_at);
                $formattedDate = $carbonDate->format('d-m-Y');
                $phoneNumber = $row->phone ? "\t" . $row->phone : "";
                $status = ($row->is_active == 1) ? 'Active' : 'In-Active';
                $blockStatus = ($row->is_block == 1) ? 'Yes' : 'No';

                $csvData[] = [
                    'Username' => $row->username,
                    'Full Name' => $row->first_name . " " . $row->last_name,
                    'Role' => $row->role_name,
                    'Email' => $row->email,
                    'Phone Number' => $phoneNumber,
                    'Status' => $status,
                    'Blocked' => $blockStatus,
                    'Created At' => $formattedDate,
                ];
            }

            $csvFileName = 'Users_' . date('d-m-Y') . '.csv';
            $csvFile = fopen('php://temp', 'w+');
            fputcsv($csvFile, array_keys($csvData[0]));

            foreach ($csvData as $row) {
                fputcsv($csvFile, $row);
            }

            rewind($csvFile);
            $csvContent = stream_get_contents($csvFile);
            fclose($csvFile);

            // Log success
            LogHelper::logSuccess(
                'success',
                'User data exported to Excel successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

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
                'An error occurred while downloading the user list.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return response()->json([
                "message" => "An error occurred while generating the CSV file.",
            ], 500);
        }
    }

    // PDF download
    public function pdf_download(Request $r)
    {
        try {
            $user = $r->user ?? "";
            $role = $r->role ?? "";
            $search = $r->input('search.value') ?? "";
            $limit = $r->length ?? null;
            $offset = $r->start ?? 0;

            $filteredData = User::filterUser($search, $limit, $offset, $user, $role);

            $total = $filteredData['totalRecords'];
            $records = $filteredData['result'];

            if (empty($records)) {
                LogHelper::logError(
                    'error',
                    'No user data found for PDF download.',
                    'No records matched the filter criteria.',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    Auth::guard('admin')->user()->id ?? null
                );
                return response()->json(["message" => "No data available for PDF download."], 204);
            }

            $htmlContent = '<style>
						table {
							width: 100%;
							border-collapse: collapse;
						}
						table, th, td {
							border: 1px solid black;
							padding: 4px;
							font-size: 12px;
							text-align: left;
						}
						.badge-soft-success {
							color: #28a745;
						}
						.badge-soft-danger {
							color: #dc3545;
						}
					</style>';

            $htmlContent .= '<table>';
            $htmlContent .= '<thead>
								<tr>
								    <th>Username</th>
									<th>Full Name</th>
									<th>Role</th>
									<th>Email</th>
									<th>Phone</th>
									<th>Status</th>
									<th>Block Status</th>
								</tr>
							</thead>';
            $htmlContent .= '<tbody>';

            foreach ($records as $row) {
                $createdDate = Carbon::parse($row->created_at)->format('d-m-Y');

                $status = $row->is_active ? '<span class="badge-soft-success">Active</span>' : '<span class="badge-soft-danger">In-Active</span>';
                $blockstatus = $row->is_block ? '<span class="badge-soft-success">Yes</span>' : '<span class="badge-soft-danger">No</span>';

                $htmlContent .= '<tr>';
                $htmlContent .= '<td>' . $row->username . '</td>';
                $htmlContent .= '<td>' . $row->first_name . " " . $row->last_name . '</td>';
                $htmlContent .= '<td>' . $row->role_name . '</td>';
                $htmlContent .= '<td>' . $row->email . '</td>';
                $htmlContent .= '<td>' . $row->phone . '</td>';
                $htmlContent .= '<td>' . $status . '</td>';
                $htmlContent .= '<td>' . $blockstatus . '</td>';
                $htmlContent .= '</tr>';
            }

            $htmlContent .= '</tbody></table>';

            $pdf = PDF::loadHTML($htmlContent);

            // Log success
            LogHelper::logSuccess(
                'success',
                'User data exported to PDF successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );

            return $pdf->download('Users.pdf');
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred while generating the user PDF.',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
            return response()->json([
                "message" => "An error occurred while generating the PDF file.",
            ], 500);
        }
    }
}
