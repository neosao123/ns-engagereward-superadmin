<?php

return [
     "server_error"=>"Server Error",
	 "subscription_package_update"=>"Subscription Package updated successfully",
     'subscription_package_update_status'=>'Subscription Package status updated successfully',
     'admin_error'=>'Something went wrong! Please try again later or contact support.',

    // Email validation
    "profile_email_required" => "Email address is required",
    "profile_email_regex" => "Please enter a valid email address",
    "profile_email_max" => "Email cannot exceed 255 characters",
    "profile_email_unique" => "This email is already registered",

    // Password validation
    "register_password_required" => "Password is required",
    "register_password_string" => "Password must be a string",
    "register_password_min" => "Password must be at least 8 characters",
    "register_password_max" => "Password cannot exceed 20 characters",
    "register_password_regex" => "Password must contain at least 1 uppercase letter, 1 number, and 1 special character.",
    "company_code_required"=>"Company code is required.",

        // Success messages
    "registration_success" => "Registration successful!",
    "account_created" => "Your account has been created successfully",
	"validation_error"=>"Validation Error",
	'account_blocked' => 'Your account has been blocked. Please contact administrator.',
	"login_successful"=>"Login successful!",
	"invalid_company"=>"Invalid Company",
	"social_media_list_exception"=>"Exception in Social Media List.",
	"register_exception"=>"Register Exception.",
	"exception_message"=>"Exception Message",
	"login_exception"=>"Login Exception.",
    "company_suspended"=>"Company is suspended.",
    'company_not_found'=>'Company not found',



	'token_required' => 'Reset token is required',
    'token_expired' => 'Password reset link has expired. Please request a new one.',
    'token_valid' => 'Token is valid. You may now reset your password.',
    'token_verification_error' => 'Token verification failed',

	//password reset error
	'password_required' => 'Password is required',
    'password_string' => 'Password must be a string',
    'password_min' => 'Password must be at least 8 characters',
    'password_max' => 'Password must not exceed 20 characters',
    'password_mismatch' => 'Password confirmation does not match',
    'password_complexity' => 'Password must contain at least 1 uppercase letter, 1 number, and 1 special character.',
    'confirm_password_required' => 'Confirm Password is required',
    'invalid_reset_token' => 'Invalid or expired reset token',
    'password_reset_success' => 'Password has been reset successfully',
    'password_reset_error' => 'Password reset failed',


     // Authentication messages
    'invalid_credential' => 'Invalid email or password',
    'account_block' => 'Your account has been blocked. Please contact the administrator.',
	'old_password_required' => 'The old password field is required.',
	'new_password_same_as_old' => 'The new password must be different from the old password.',
    'confirm_password_required' => 'The password confirmation field is required.',
	// Success messages
    'password_change_success' => 'Password changed successfully.',

    // Error messages
    'incorrect_old_password' => 'The provided old password is incorrect.',
    'password_change_error' => 'An error occurred while changing the password.',
    'server_error' => 'Server error. Please try again later.',

    // Other messages (from your reset password API)
    'token_required' => 'Token is required.',
    'password_required' => 'Password is required.',
    'invalid_reset_token' => 'Invalid or expired reset token.',
    'password_reset_success' => 'Password reset successfully.',
    'password_reset_error' => 'An error occurred while resetting the password.',

    'password_reset_error' => 'An error occurred while resetting the password.',

    // App Version Update
    'setting_id_required' => 'Setting ID is required.',
    'setting_id_integer' => 'Setting ID must be an integer.',
    'setting_value_required' => 'Setting value is required.',
    'is_update_compulsory_required' => 'Update compulsory flag is required.',
    'current_version_update' => 'Current version updated successfully.',
];
