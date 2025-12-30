<?php

/** --------------------------------------------------------------------------------
 * This controller manages all the profile operations
 *
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Helpers\LogHelper;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use DB;

class ProfileController extends Controller
{


    /**
     * Display the authenticated user's profile page
     *
     * Handles authentication check, fetches user details, and renders profile view
     * Includes comprehensive error handling and logging
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */

    public function index()
    {
        try {
            if (!(Auth::user())) {
                // Unauthorized access log (optional)
                LogHelper::logError(
                    'error',
                    'Unauthorized access attempt to profile page',
                    'No user authenticated',
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    request()->path(),
                    null
                );
                return redirect('/');
            }

            $details = Auth::user();

            // Success log
            LogHelper::logSuccess(
                'success',
                'User profile fetched successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $details->id
            );

            return view('main.profile.index', compact('details'));
        } catch (\Exception $exception) {
            // Error log
            LogHelper::logError(
                'exception',
                'An error occurred while fetching the profile',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id
            );

            return redirect()->back()->with('error', 'An error occurred while fetching the profile.');
        }
    }


    /**
     * Update user profile information
     *
     * Handles validation and updating of user profile data including:
     * - Personal details (first/last name)
     * - Contact information (phone, email)
     * - Profile avatar image
     * Includes comprehensive validation and error handling
     *
     * @param Request $r The incoming request with profile data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $r)
    {
        $id = Auth::user()->id;

        $validator = Validator::make($r->all(), [
            'first_name' => [
                'required',
                'regex:/^[A-Za-z\s]+$/',
                'min:2',
                'max:150'
            ],
            'last_name' => [
                'required',
                'regex:/^[A-Za-z\s]+$/',
                'min:2',
                'max:150'
            ],
            'phone' => [
                'required',
                function ($attribute, $value, $fail) use ($r, $id) {
                    if (empty($value)) return;

                    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                    try {
                        $countryCode = $r->input('phone_country');
                        $numberProto = $phoneUtil->parse($value, $countryCode);

                        if (!$phoneUtil->isValidNumber($numberProto)) {
                            $fail('Invalid phone number for selected country');
                        }

                        // Check uniqueness with country code
                        $formattedNumber = $phoneUtil->format(
                            $numberProto,
                            \libphonenumber\PhoneNumberFormat::E164
                        );

                        $exists = DB::table('users')
                            ->where('phone', $formattedNumber)
                            ->where('phone_country', $countryCode)
                            ->whereNull('deleted_at')
                            ->where('id', '!=', $id)
                            ->exists();

                        if ($exists) {
                            $fail('This phone number already exists');
                        }
                    } catch (\libphonenumber\NumberParseException $e) {
                        $fail('Invalid phone number format');
                    }
                }
            ],
            'phone_country' => 'required|string|size:2',
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'min:2',
                'max:150',
                Rule::unique('users')->where(function ($query) use ($id) {
                    return $query->whereNull('deleted_at')
                        ->where("id", '!=', $id);
                })
            ],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ], [
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'The first name must only contain alphabets and spaces.',
            'first_name.min' => 'The First name must be at least 2 characters long.',
            'first_name.max' => 'The First name cannot exceed 150 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.regex' => 'The last name must only contain alphabets and spaces.',
            'last_name.min' => 'The Last name must be at least 2 characters long.',
            'last_name.max' => 'The Last name cannot exceed 150 characters.',
            'phone.required' => 'The Phone number is required.',
            'phone_country.required' => 'Country code is required.',
            'phone_country.size' => 'Invalid country code format.',
            'email.required' => 'The Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'email.min' => 'The Email must be at least 2 characters long.',
            'email.max' => 'The Email cannot exceed 150 characters.',
            'avatar.image' => 'The file must be an image.',
            'avatar.mimes' => 'The image must be of type: jpeg, png, jpg, gif.',
        ]);

        if ($validator->fails()) {
            LogHelper::logError(
                'Validation failed while updating user profile',
                json_encode($validator->errors()->all()),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                $id
            );
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 200);
        }

        try {

            $phoneUtil = PhoneNumberUtil::getInstance();
            $countryCode = $r->input('phone_country');
            $numberProto = $phoneUtil->parse($r->phone, $countryCode);

            $data = [
                'first_name' => $r->first_name,
                'last_name' => $r->last_name,
                'phone' => $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164),
                'email' => $r->email
            ];

            // Check if a file was uploaded
            if ($r->hasFile('avatar')) {
                $file = $r->file('avatar');
                $path = Storage::disk('public')->putFileAs('avatar', $file, "avatar-" . time() . "." . $file->getClientOriginalExtension());
                $data['avatar'] = $path;
            }

            $userid = Auth::user()->id;

            // profile update
            $user = User::find($userid);
            $user->update($data);

            // Success log
            LogHelper::logSuccess(
                'success',
                'The profile updated successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $userid
            );



            // Return success response

            return response()->json([
                'status' => 200,
                'message' => 'Profile Updated Successfully.'
            ], 200);
        } catch (\Exception $exception) {
            // Error log
            LogHelper::logError(
                'exception',
                'An error occurred while update the profile',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id
            );

            // Return error response to the user
            return redirect()->back()->with('error', 'An error occurred while update the user.');
        }
    }



    /**
     * Delete user's avatar image
     *
     * Handles removal of user's profile picture by:
     * 1. Deleting the physical file from storage
     * 2. Clearing the avatar reference in database
     * Includes success/error logging and appropriate responses
     *
     * @return \Illuminate\Http\RedirectResponse
     */

    public function deleteAvatar()
    {
        try {
            $user = Auth::user();
            $path = $user['avatar'];

            Storage::disk('public')->delete($path);

            $getUser = User::find($user['id']);
            $getUser->update(['avatar' => null]);

            // Success log
            LogHelper::logSuccess(
                'success',
                'Avatar deleted successfully.',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                $user['id']
            );

            // Return success response
            return redirect('profile')->with('success', 'Avatar deleted successfully');
        } catch (\Exception $exception) {
            // Error log
            LogHelper::logError(
                'exception',
                'An error occurred while the avatar delete',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id
            );

            // Return error response
            return redirect()->back()->with('error', 'An error occurred while the avatar delete.');
        }
    }

    /**
     * Display the change password form
     *
     * Shows the password change view to authenticated users
     * Handles any errors that may occur during view rendering
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */

    public function changePassword()
    {
        try {
            return view('main.change-password.index');
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'An error occurred while showing the change password page',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path()
            );

            return redirect()->back()->with('error', 'An error occurred while showing the change password page.');
        }
    }


    /**
     * Update user password
     *
     * Handles password change request with validation and security checks:
     * 1. Validates old password matches current password
     * 2. Validates new password meets requirements
     * 3. Updates password if all checks pass
     * Includes comprehensive logging for all scenarios
     *
     * @param Request $r The incoming request with password data
     * @return \Illuminate\Http\RedirectResponse
     */

    public function updatePassword(Request $r)
    {
        $r->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8',
            'password_confirmation' => 'required|same:new_password',
        ], [
            'old_password.required' => 'The old password is required.',
            'new_password.required' => 'The new password is required.',
            'new_password.min' => 'The new password must be at least :min characters.',
            //'new_password.max' => 'The new password must not be more than :max characters.',
            'password_confirmation.required' => 'The password confirmation is required.',
            'password_confirmation.same' => 'The password confirmation must match the new password.',
            //'new_password.regex' => 'Enter valid password',
            //'password_confirmation.regex' => 'Enter valid confirm password',
        ]);

        try {
            // Get user
            $user = User::find(Auth::user()->id);

            if ($user) {
                if (Hash::check($r->old_password, $user->password)) {
                    $user->update(['password' => Hash::make($r->new_password)]);

                    //after update password session will be removed from all devices

                    DB::table('sessions')
                        ->where('user_id',  $user->id)
                        ->where('id', '!=', session()->getId())
                        ->delete();



                    // Success log
                    LogHelper::logSuccess(
                        'success',
                        'The password changed successfully.',
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path(),
                        $user->id
                    );

                    return redirect('change-password')->with('success', 'The password updated successfully.');
                } else {
                    // Error log - invalid old password
                    LogHelper::logError(
                        'validation_error',
                        'Old password does not match.',
                        'User entered invalid old password',
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path(),
                        $user->id
                    );

                    return back()->with('error', 'The old password is not valid.');
                }
            } else {
                // Error log - user not found
                LogHelper::logError(
                    'validation_error',
                    'User does not exist.',
                    'No user found with id ' . Auth::user()->id,
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    $r->path(),
                    Auth::user()->id
                );

                return back()->with('error', 'User does not exist.');
            }
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'An error occurred while password change',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                Auth::user()->id
            );

            return redirect()->back()->with('error', 'An error occurred while password change.');
        }
    }
}
