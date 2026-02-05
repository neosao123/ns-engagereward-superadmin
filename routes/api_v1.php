<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Api\V1\OnboardingController;
use App\Http\Controllers\Api\V1\BookdemoController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PaymentController;
Route::post('/book-company-demo',[BookdemoController::class, 'book_company_demo']);
Route::get('/payment-setting', [PaymentController::class, 'paymentSetting']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/check-company-code', [AuthController::class, 'check_company_code']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forget-password', [AuthController::class, 'forget_password']);
Route::post('/reset-password', [AuthController::class, 'reset_password']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/company-update', [CompanyController::class, 'update_company_details']);
Route::get('/subscription-list', [SubscriptionController::class, 'list']);
Route::post('/subscription-purchase', [SubscriptionController::class, 'subscription_purchase']);
Route::post('/subscription-package/update/status', [SubscriptionController::class, 'update_purchase_package']);
Route::post('/update/paypal-order-status', [SubscriptionController::class, 'update_paypal_order_status']);
Route::post('/subscription-package/update/id', [SubscriptionController::class, 'update_purchase_package_id']);

// ONBOARD
Route::group(['prefix' => 'onboard'], function () {
    Route::get('steps',[OnboardingController::class,'trial']);
    Route::get('step-one', [OnboardingController::class, 'step_one']);
    Route::get('step-two', [OnboardingController::class, 'step_two']);
    Route::get('step-three', [OnboardingController::class, 'step_three']);
    Route::get('step-four', [OnboardingController::class, 'step_four']);
    Route::get('step-five', [OnboardingController::class, 'step_five']);
});
