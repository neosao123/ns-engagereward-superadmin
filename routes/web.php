<?php

//
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SocialMediaAppController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionGroupController;
use App\Http\Controllers\UserPermissionsController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CronjobController;
use App\Http\Controllers\PaymentSettingController;
use App\Http\Controllers\WebhookController;

use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::post('/payment/callback',  [WebhookController::class, 'stripewebhook']);

Route::get('clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    echo 1;
});
Route::get('/expire-package', [CronjobController::class, 'subscription_package_expire']);

Route::get('/', function () {
    return view('login');
});

/*
Route::get('/reset-password', function () {
    $user = User::find(1);

    if (!$user) {
        return 'User not found.';
    }

    $user->password = Hash::make('123456');
    $user->save();

    return 'Password updated successfully for user ID 1.';
});
*/

/*
 * Access Storage Files
 */
Route::get("storage-bucket", function (Request $request) {
    return response()->file(storage_path('app/public/' . $request->path));
});



/** --------------------------------------------------------------------------------------------------
 * Admin Routes
 * seemashelar@neosao
 * --------------------------------------------------------------------------------------------------- */
Route::get('/updatepassword', [AuthController::class, 'updatePassword']);

//forgot-password
Route::get('/forgot-password', [AuthController::class, 'reset']);
Route::post('/reset-password', [AuthController::class, 'reset_password']);
Route::get('/verify-token/{token}', [AuthController::class, 'verify_token_link']);
Route::post('/recovers-password', [AuthController::class, 'update_password']);


Route::group(['middleware' => ['PreventBack']], function () {

	/** --------------------------------------------------------------------------------------------------
     * login + logged out
     *
     * --------------------------------------------------------------------------------------------------- */

    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

	 Route::group(['middleware' => ['admin']], function () {

		Route::get('/welcome', [DashboardController::class, 'welcome']);
		Route::get("/dashboard", [DashboardController::class, 'index']);

		Route::group(['prefix' => 'configuration'], function () {

            /** --------------------------------------------------------------------------------------------------
             * Role
             *
             * --------------------------------------------------------------------------------------------------- */
            Route::group(['prefix' => 'role'], function () {
                Route::get('/', [RoleController::class, 'index']);
                Route::get('/list', [RoleController::class, 'list']);
                Route::get('/add', [RoleController::class, 'add']);
                Route::post('/store', [RoleController::class, 'store']);
                Route::get('/edit', [RoleController::class, 'edit']);
                Route::post('/update', [RoleController::class, 'update']);
                Route::get('/delete/{id}', [RoleController::class, 'destroy']);
            });
            /*Route::group(['prefix' => 'role'], function () {
				Route::get('/list', [RoleController::class, 'list']);
			});
			Route::resource('role', RoleController::class);*/

			 /** --------------------------------------------------------------------------------------------------
             * permission groups
             * --------------------------------------------------------------------------------------------------- */
            Route::group(['prefix' => '/permission-groups'], function () {
                Route::get('/', [PermissionGroupController::class, 'index'])->middleware(['permission:PermissionGroup.List,admin']);
                Route::get('/create', [PermissionGroupController::class, 'create'])->middleware(['permission:PermissionGroup.Create,admin']);
                Route::post('/store', [PermissionGroupController::class, 'store']);
            });

            /** --------------------------------------------------------------------------------------------------
             * permissions
             * --------------------------------------------------------------------------------------------------- */

            Route::group(['prefix' => '/permissions'], function () {
                Route::get('/', [PermissionController::class, 'index'])->middleware(['permission:Permissions.List,admin']);
                Route::post('/store', [PermissionController::class, 'store'])->middleware(['permission:Permissions.Create,admin']);
            });

        });


		 /** --------------------------------------------------------------------------------------------------
         * user master
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => 'users'], function () {
            Route::get('/exceldownload', [UserController::class, 'excel_download']);
            Route::get('/pdfdownload', [UserController::class, 'pdf_download']);
            Route::get('list', [UserController::class, 'list']);
			Route::get('/fetch/role', [UserController::class, 'get_role']);
            Route::get('/fetch/users', [UserController::class, 'get_users']);
            Route::get('/delete/avatar/{id}', [UserController::class, 'delete_avatar']);
            Route::get('/block/{id}', [UserController::class, 'block_unblock_user']);

        });

        Route::resource('users', UserController::class);
        /** --------------------------------------------------------------------------------------------------
         * access rights for user
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => '/user/{id}'], function () {
            Route::get('/permissions', [UserPermissionsController::class, 'index']);
            Route::get('/set-permission', [UserPermissionsController::class, 'setPermission']);
        });


		//profile update
	    Route::group(['prefix' => 'profile'], function () {
            Route::get('/', [ProfileController::class, 'index']);
            Route::post('/update', [ProfileController::class, 'update']);
            Route::get('/delete/avatar', [ProfileController::class, 'deleteAvatar']);
        });

		 /** --------------------------------------------------------------------------------------------------
         * change password
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => 'change-password'], function () {
            Route::get('/', [ProfileController::class, 'changePassword']);
            Route::post('/update', [ProfileController::class, 'updatePassword']);
        });

		/** --------------------------------------------------------------------------------------------------
         * social media app
         * --------------------------------------------------------------------------------------------------- */

		Route::get('social-media-apps/list', [SocialMediaAppController::class, 'list']);
        Route::get('social-media-apps/delete/logo/{id}', [SocialMediaAppController::class, 'delete_logo']);
        Route::resource('social-media-apps', SocialMediaAppController::class);

		/** --------------------------------------------------------------------------------------------------
         * company
         * --------------------------------------------------------------------------------------------------- */


		Route::group(['prefix' => 'company'], function () {
			Route::get('integration-credentials/{companyId}/add', [CompanyController::class, 'add_integration_credentials']);
			//Route::get('integration-credentials/{socialId}/{companyId}/{type}/add', [CompanyController::class, 'add_integration_credentials']);
			Route::post('store-integration-credentials', [CompanyController::class, 'store_integration_credentials']);
			Route::delete('integration-credentials/{id}', [CompanyController::class, 'integration_credential_delete']);
			Route::post('update-status/{id}', [CompanyController::class, 'update_status']);
			Route::get('exceldownload', [CompanyController::class, 'excel_download']);
			Route::get('pdfdownload', [CompanyController::class, 'pdf_download']);
			Route::get('list', [CompanyController::class, 'list']);
			Route::get('fetch/company_key', [CompanyController::class, 'company_key']);
			Route::get('fetch/company_name', [CompanyController::class, 'company_name']);
			Route::get('fetch/email', [CompanyController::class, 'company_email']);
			Route::get('fetch/phone', [CompanyController::class, 'company_phone']);
			Route::get('/fetch/subscriptions', [CompanyController::class, 'subscription_title']);
			Route::get('/fetch/subscriptions-renew', [CompanyController::class, 'subscription_for_renew']);
			Route::get('country-code', [CompanyController::class, 'country_list']);
			Route::post('add/basic-info', [CompanyController::class, 'add_basic_info']);
			Route::post('add/address-info', [CompanyController::class, 'add_address_info']);
			Route::post('add/social-info', [CompanyController::class, 'add_social_info']);
			Route::post('add/sub-info', [CompanyController::class, 'add_sub_info']);
			Route::post('add/document-info', [CompanyController::class, 'add_document_info']);

			Route::post('update/basic-info', [CompanyController::class, 'update_basic_info']);
			Route::post('update/address-info', [CompanyController::class, 'update_address_info']);
			Route::post('update/social-info', [CompanyController::class, 'update_social_info']);
			Route::post('update/document-info', [CompanyController::class, 'update_document_info']);

		    Route::get('delete/logo/{id}', [CompanyController::class, 'delete_company_logo']);
		    Route::delete('document/{id}', [CompanyController::class, 'document_delete']);

            Route::get('{id}/setup', [CompanyController::class, 'setup']);
            Route::get('{id}/setup/action', [CompanyController::class, 'setupAction']);

			Route::post('update/address-info', [CompanyController::class, 'update_address_info']);

		    Route::get('subscription-plan', [CompanyController::class, 'subscription_plan']);
		    Route::put('update/subscription-plan/{id}', [CompanyController::class, 'subscription_update']);
			Route::post('add/subscription-plan/{id}', [CompanyController::class, 'subscription_add']);
			Route::post('suspend/subscription-plan/{id}', [CompanyController::class, 'subscription_update_status']);
		});

		Route::resource('company', CompanyController::class);

		/** --------------------------------------------------------------------------------------------------
         * setting
        * --------------------------------------------------------------------------------------------------- */
		Route::group(['prefix' => 'setting'], function () {
	 	    Route::get('list', [SettingController::class, 'list']);
			Route::get('delete/logo/{id}', [SettingController::class, 'delete_logo']);
		});
		Route::resource('setting', SettingController::class);



	     /** --------------------------------------------------------------------------------------------------
         * subscription paln
         * --------------------------------------------------------------------------------------------------- */

        Route::group(['prefix' => 'subscription-plan'], function () {
            Route::get('list', [SubscriptionPlanController::class, 'list']);
			Route::get('fetch/social-media-app', [SubscriptionPlanController::class, 'social_media_app']);
			Route::get('fetch/currency', [SubscriptionPlanController::class, 'currency_list']);
        });

        Route::resource('subscription-plan', SubscriptionPlanController::class);


        /** --------------------------------------------------------------------------------------------------
         * payment setting
        * --------------------------------------------------------------------------------------------------- */
		Route::get('/payment-setting', [PaymentSettingController::class, 'create']);
        Route::post('/payment-setting/store', [PaymentSettingController::class, 'store']);

	 });

});

/*Route::fallback(function (){
    return view('unauthorize');
});*/
