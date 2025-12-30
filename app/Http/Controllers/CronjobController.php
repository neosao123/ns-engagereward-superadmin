<?php
/** --------------------------------------------------------------------------------
 * This controller manages cron job which is used to expiry package
 *
 * @author     Neosao Services Pvt Ltd.
 *----------------------------------------------------------------------------------*/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\LogHelper;
use App\Models\Company;
use App\Models\SubscriptionPurchase;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\HandlesAdminApiRequests;
use App\Traits\HandlesApiResponses;

class CronjobController extends Controller
{
    use HandlesAdminApiRequests, HandlesApiResponses;

    public function subscription_package_expire(Request $request)
    {
        try {
            LogHelper::logSuccess('Cron Job Started', 'Cron job "subscription_package_expire" started.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

            Company::where('is_active', 1)
                ->where("account_status", "active")
                ->orderBy('id')
                ->chunk(100, function (Collection $companies) {
                    foreach ($companies as $company) {
                        LogHelper::logSuccess('Processing Company', "Processing Company: ID {$company->id}, Code: {$company->company_code}", __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                        // Current active package
                        $package = SubscriptionPurchase::query()
                            ->where('is_active', 1)
                            ->where('status', 'active')
                            ->where('company_id', $company->id)
                            ->where('payment_status', 'paid')
                            ->latest('id')
                            ->first();

                        if ($package) {
                            LogHelper::logSuccess('Active Package Found', "Active package found: ID {$package->id}, To Date: {$package->to_date}", __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                        } else {
                            LogHelper::logSuccess('No Active Package', "No active package found for Company ID {$company->id}", __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                        }

                        // Expire package if past to_date
                        if ($package && $package->to_date && now()->greaterThanOrEqualTo($package->to_date)) {
                            $package->update(['is_active' => 0, 'status' => 'expired']);
                            LogHelper::logSuccess('Package Expired', "Package ID {$package->id} for Company ID {$company->id} marked as expired.", __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                            $adminData = [
                                'package_id' => $package->id,
                                'status' => 'expired',
                                'is_active' => 0
                            ];

                            $response = $this->makeSecurePostApiRequest(
                                strtolower($company->company_code) . '/api/v1/subscription-plan-update',
                                $adminData
                            )->throw();

                            $result = $response->json();

                            if ($result['status'] == 200) {
                                LogHelper::logSuccess('API Update Success', "Package ID {$package->id} status updated successfully via API.", __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                            } else {
                                LogHelper::logError('API Update Failed', "Failed to update package ID {$package->id} via API. Response: " . json_encode($result), '', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                            }
                        }

                        // Activate next package if exists
                        $nextPackage = SubscriptionPurchase::query()
                            ->where([
                                ['is_active', 1],
                                ['status', 'inactive'],
                                ['payment_status', 'paid']
                            ])
                            ->where('company_id', $company->id)
                            ->when($package, function ($query) use ($package) {
                                return $query->where('id', '>', $package->id);
                            })
                            ->orderBy('id', 'asc')
                            ->first();

                        if ($nextPackage) {
                            $nextPackage->update(['is_active' => 1, 'status' => 'active']);
                            LogHelper::logSuccess('Next Package Activated', "Next package ID {$nextPackage->id} for Company ID {$company->id} activated.", __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

                            $nextPackageAdminData = [
                                'company_id' => $company->id,
                                'package_id' => $nextPackage->id,
                                'status' => 'active',
                                'is_active' => 1
                            ];

                            $response = $this->makeSecurePostApiRequest(
                                strtolower($company->company_code) . '/api/v1/subscription-plan-update',
                                $nextPackageAdminData
                            )->throw();

                            $result = $response->json();

                            if ($result['status'] == 200) {
                                LogHelper::logSuccess('API Next Package Success', "Next package ID {$nextPackage->id} status updated successfully via API.", __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                            } else {
                                LogHelper::logError('API Next Package Failed', "Failed to update next package ID {$nextPackage->id} via API. Response: " . json_encode($result), '', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                            }
                        } else {
                            LogHelper::logSuccess('No Next Package', "No next package available to activate for Company ID {$company->id}.", __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');
                        }
                    }
                });

            LogHelper::logSuccess('Cron Job Completed', 'Cron job "subscription_package_expire" completed successfully.', __FUNCTION__, basename(__FILE__), __LINE__, __FILE__, '');

        } catch (\Exception $exception) {
            LogHelper::logError(
                'Cron Job Exception',
                'An exception occurred during cron job execution: ' . $exception->getMessage(),
                '',
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                request()->path(),
                Auth::guard('admin')->user()->id ?? null
            );
        }
    }
}
