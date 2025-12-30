<?php

/** --------------------------------------------------------------------------------
 * This controller manages all the auth operations
 *
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Mail;
use Illuminate\Support\Facades\Log;
use App\Helpers\LogHelper;
use App\Mail\ForgotAdminEmail;
use Illuminate\Support\Facades\Session;
use DB;

class AuthController extends Controller
{
    /**
     * Display the login page for admin users
     *
     * This function checks if an admin is already authenticated. If yes, it redirects to dashboard.
     * Otherwise, it shows the login page. It also logs successful access or any errors that occur.
     *
     *
     * @param Request $r The incoming request object
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Returns either login view or redirects to dashboard/back
     */

    public function index(Request $r)
    {
        try {
            // Check if admin is already authenticated
            if (Auth::guard('admin')->check()) {
                if (Auth::guard('admin')->user()->id != "") {
                    return redirect('/dashboard');
                }
            }

            // Log success for reaching the login view
            LogHelper::logSuccess(
                'success',
                'Login page loaded successfully',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path()
            );

            return view('login');
        } catch (\Exception $exception) {
            // Log the error using LogHelper
            LogHelper::logError(
                'exception',
                'An error occurred while loading the login page',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path()
            );

            // Redirect back with error message
            return redirect()->back()->with('error', 'An error occurred while loading the login page.');
        }
    }

    /**
     * Handles admin login authentication process
     *
     * Validates credentials, checks account status (active/blocked), implements remember-me functionality,
     * and redirects based on role permissions. Logs all authentication attempts.
     *
     * @param Request $r The incoming HTTP request containing login credentials
     * @return \Illuminate\Http\RedirectResponse Redirects to dashboard on success or back to login with errors
     * @throws \Exception Logs any unexpected errors during the authentication process
     */
    public function login(Request $r)
    {
        // Validate required fields
        $r->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        try {
            if ($r->isMethod('post')) {
                $email = $r->input('email');
                $password = $r->input('password');
                $remember = $r->input('rememberme') == true ? '1' : '0';

                $result = User::where(['email' => $email])
                    ->whereNull("deleted_at")
                    ->first();

                if (empty($result)) {
                    $r->session()->flash('fail', 'Please Enter Valid Email & Password');

                    LogHelper::logError(
                        'validation_error',
                        'Login failed: Email not found',
                        'User not found with email: ' . $email,
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path()
                    );

                    return redirect('login');
                }

                if ($result->is_active == 0) {
                    $r->session()->flash('fail', 'Your account is inactive, please contact the administrator to activate it.');

                    LogHelper::logError(
                        'condition_failed',
                        'Login failed: Inactive account',
                        'Inactive account: ' . $email,
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path(),
                        $result->id
                    );

                    return redirect('login');
                }


                if ($result->is_block == 1) {
                    $r->session()->flash('fail', 'Your account is blocked. Please contact the administrator for further assistance.');

                    LogHelper::logError(
                        'condition_failed',
                        'Login failed: Blocked account',
                        'Blocked account: ' . $email,
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path(),
                        $result->id
                    );

                    return redirect('login');
                }

                if (Auth::guard('admin')->attempt(['email' => $email, 'password' => $password])) {
                    Auth::login($result);
                    $r->session()->put('SUPERUSER_LOGIN', true);

                    // Remember me functionality
                    if ($remember == '1') {
                        Cookie::queue('email', $email, 5256000); // 10 years in minutes
                        Cookie::queue('password', $password, 5256000);
                    } else {
                        Cookie::queue('email', '');
                        Cookie::queue('password', '');
                    }

                    LogHelper::logSuccess(
                        'success',
                        'Admin login successful',
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path(),
                        $result->id
                    );

                    $role = Auth::guard('admin')->user()->role_id;
                    if ($role == 1) {
                        return redirect('dashboard');
                    } else {
                        if (Auth::guard('admin')->user()->can('Dashboard.View')) {
                            return redirect('dashboard');
                        } else {
                            return redirect('welcome');
                        }
                    }
                }

                $r->session()->flash('error', 'Invalid Email or Password.');

                LogHelper::logError(
                    'validation_error',
                    'Login failed: Invalid password',
                    'Incorrect password for: ' . $email,
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    $r->path(),
                    $result->id
                );

                return redirect('login');
            }
        } catch (\Exception $ex) {
            LogHelper::logError(
                'exception',
                'An error occurred during admin login',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path()
            );

            return redirect('login')->with('error', 'Something went wrong. Please try again.');
        }
    }


    /**
     * Handles admin logout process
     *
     * Terminates the admin session, clears authentication guards, removes session variables,
     * and logs the logout activity. Returns to login page with success/error notification.
     *
     * @param Request $r The incoming HTTP request
     * @return \Illuminate\Http\RedirectResponse Redirects to login page after logout
     * @throws \Exception Logs any unexpected errors during logout process
     */
    public function logout(Request $r)
    {
        try {
            // Get admin user ID before logout
            $adminId = Auth::guard('admin')->id();

            // Logout the user
            Auth::logout();
            Auth::guard("admin")->logout();
            session()->forget('SUPERUSER_LOGIN');

            // Flash message
            $r->session()->flash('success', 'Successfully logged out');

            // Log success
            LogHelper::logSuccess(
                'success',
                'Admin logged out successfully',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path(),
                $adminId
            );

            return redirect('login');
        } catch (\Exception $e) {
            // Log exception using consistent variable name
            LogHelper::logError(
                'exception',
                'An error occurred while the admin logged out',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path()
            );

            return redirect('login')->with('error', 'An error occurred while logging out.');
        }
    }


    /**
     * Displays the password reset request form
     *
     * @param Request $r The incoming HTTP request
     * @return \Illuminate\View\View Returns the password reset view
     */
    public function reset(Request $r)
    {
        return view('layout.auth.reset');
    }

    /**
     * Handles password reset request
     *
     * Processes email submission, generates reset token, sends email with reset link,
     * and stores token in database. Logs all activities and errors.
     *
     * @param Request $r The incoming HTTP request containing user email
     * @return \Illuminate\Http\RedirectResponse Redirects with status message
     * @throws \Exception Logs email sending or database errors
     */
    public function reset_password(Request $r)
    {
        try {
            $email = $r->input('email');
            $result = User::where("email", $email)->first();

            if ($result) {
                // Generate reset token
                $token = $this->random_characters(5) . date('Hdm');
                $sendLink = url('verify-token/' . $token);

                // Prepare email content
                $details = [
                    'username' => $result->first_name,
                    'title' => 'Mail from EngageReward',
                    'link' => $sendLink,
                ];

                // Send email
                Mail::to($email)->send(new ForgotAdminEmail($details));

                // Save token in DB
                $resultAfterMail = $result->update(['reset_token' => $token]);

                if ($resultAfterMail) {
                    // Log success
                    LogHelper::logSuccess(
                        'success',
                        'Reset password link sent successfully',
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path(),
                        $result->id
                    );

                    $r->session()->flash('success', 'Reset Link sent on your email, Please check your email.');
                    return redirect('/forgot-password');
                } else {
                    LogHelper::logError(
                        'database_error',
                        'Failed to update reset token',
                        'Failed to save reset_token for email: ' . $email,
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path(),
                        $result->id
                    );

                    $r->session()->flash('error', 'Some error occurred.');
                    return redirect('/forgot-password');
                }
            } else {
                // Log invalid email
                LogHelper::logError(
                    'validation_error',
                    'Reset password failed: Email not found',
                    'No user found with email: ' . $email,
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    $r->path()
                );

                $r->session()->flash('error', 'No user found with the provided email!');
                return redirect('/forgot-password');
            }
        } catch (\Exception $e) {
            // Log exception
            LogHelper::logError(
                'exception',
                'An error occurred during password reset',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path()
            );

            return redirect('/forgot-password')->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Verifies password reset token validity
     *
     * Checks if provided token exists in database and shows password update form
     * or returns error if token is invalid/expired.
     *
     * @param Request $r The incoming HTTP request containing token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Returns password update view or redirects with error
     * @throws \Exception Logs token verification errors
     */
    public function verify_token_link(Request $r)
    {
        try {
            $token = $r->token;
            $result = User::where("reset_token", $token)->first();
            if ($result) {
                return view('layout.auth.verify', compact('result'));
            } else {
                LogHelper::logError(
                    'validation_error',
                    'Invalid or expired password reset token',
                    'Invalid token: ' . $token,
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    $r->path()
                );

                $r->session()->flash('message', 'Password Reset Link is Expired. Please click on Forgot Password Again to Continue.');
                return redirect('/login');
            }
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'An error occurred while verifying the password reset token',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path()
            );

            return redirect('/login')->with('error', 'Something went wrong. Please try again.');
        }
    }


    /**
     * Updates user password after verification
     *
     * Validates password requirements, checks token validity, updates password in database,
     * and clears the reset token. Logs all activities and errors.
     *
     * @param Request $r The incoming HTTP request with new password
     * @return \Illuminate\Http\RedirectResponse Redirects to login with status message
     * @throws \Exception Logs password update errors
     */
    public function update_password(Request $r)
    {
        $token = $r->input('token');

        $rules = [
            'password' => 'min:8|confirmed|required',
            'password_confirmation' => 'min:8|required',
        ];

        $messages = [
            'password.required' => 'Password is required',
            'password.min' => 'Password should contain minimum 8 character.',
            'password.confirmed' => 'Password does not match with confirm password',
        ];

        $this->validate($r, $rules, $messages);

        $getResult = User::where("reset_token", $token)->first();

        try {
            if ($getResult) {

                $data = [
                    'password' => Hash::make($r->input('password')),
                    'reset_token' => null,
                ];

                $result = $getResult->update($data);

                if ($result) {

                    DB::table('sessions')
                        ->where('user_id',   $getResult->id)
                        ->where('id', '!=', session()->getId())
                        ->delete();

                    // Log success
                    LogHelper::logSuccess(
                        'success',
                        'Password reset successfully',
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path(),
                        $getResult->id
                    );

                    $r->session()->flash('message', 'Password Reset Successfully.. Please Login to Continue');
                    return redirect('/login');
                } else {
                    LogHelper::logError(
                        'database_error',
                        'Failed to update password in database',
                        'Update failed for user ID: ' . $getResult->id,
                        __FUNCTION__,
                        basename(__FILE__),
                        __LINE__,
                        $r->path(),
                        $getResult->id
                    );

                    $r->session()->flash('message', 'Problem During Reset Password.. Please Try Again');
                    return redirect('/login');
                }
            } else {
                LogHelper::logError(
                    'validation_error',
                    'Reset link token invalid or expired',
                    'Invalid token: ' . $token,
                    __FUNCTION__,
                    basename(__FILE__),
                    __LINE__,
                    $r->path()
                );

                $r->session()->flash('message', 'Reset Link is broken! Please try again...');
                return redirect('/login');
            }
        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'An error occurred while updating the password',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $r->path()
            );

            return redirect('/login')->with('error', 'Something went wrong. Please try again.');
        }
    }


    public function random_characters($n)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}
