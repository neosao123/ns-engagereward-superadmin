<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;



class BookdemoController extends Controller
{
	public function book_company_demo(Request $request)
	{
		try {
			// Validation rules for demo booking
			$rules = [
				'company_name' => 'required|string|max:800',
				'company_email' => 'required|email|max:255',
				'company_size' => 'required',
				'message'=>'nullable|max:800',
			];

			$messages = [
				'company_name.required' => 'The company name is required.',
				'company_name.string' => 'The company name must be a valid string.',
				'company_name.max' => 'The company name may not be greater than 800 characters.',

				'company_email.required' => 'The email field is required.',
				'company_email.email' => 'Please enter a valid email address.',
				'company_email.max' => 'The email may not be greater than 255 characters.',

				'company_size.required' => 'The company size is required.',
				'message.max' => 'Message may not be greater than 800 characters.',
			];

			$validator = Validator::make($request->all(), $rules, $messages);

			if ($validator->fails()) {
				return response()->json([
					"status" => 500,
					"message" => $validator->errors()->first()
				], 200);
			}

			// Extract fields
			$companyName = $request->company_name;
			$email = $request->company_email;
			$companySize = $request->company_size;
			$message=$request->message;

			// Email to the user
			Mail::send([], [], function ($mail) use ($companyName, $email) {
				$mail->to($email)
					->subject('Demo Booking Confirmation')
					->html("
						<p>Dear {$companyName},</p>
						<p>Thank you for booking a demo with us. Our team will get in touch shortly to schedule your session.</p>
						<p>We look forward to connecting with you!</p>
					");
			});

			// Email to admin
		   $adminEmail = env('SUPPORT_MAIL');
           // $adminEmail = 'testing.neosaoservices@gmail.com';
			Mail::send([], [], function ($mail) use ($companyName, $email, $companySize, $adminEmail,$message) {
				$mail->to($adminEmail)
					->subject('New Demo Booking Request')
					->html("
						<p>A new demo booking request has been received:</p>
						<p><strong>Company Name:</strong> {$companyName}</p>
						<p><strong>Email:</strong> {$email}</p>
						<p><strong>Company Size:</strong> {$companySize}</p>
						<p><strong>Message:</strong> {$message}</p>
					");
			});

			return response()->json([
				"status" => 200,
				'message' => 'Thank you! Your demo booking request submitted successfully.'
			], 200);

		} catch (\Exception $ex) {
			LogHelper::logError(
                'exception',
               'Failed to booking demo',
                $ex->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ''
            );
			return response()->json([
				'status' => 400,
				'message' => 'Something went wrong: ' . $ex->getMessage()
			], 400);
		}
	}


}
