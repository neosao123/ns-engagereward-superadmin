<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;

class PackageDbService
{
    /**
     * Update package payment status in Company DB
     */
    public function updatePackagePaymentStatus($company, $payment_id, $status, $isActive, $packageStatus, $webhookResponse)
    {

       try {
            $connectionConfig = [
                'driver'    => 'mysql',
                'host'      => '127.0.0.1',
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
            ];

            if ($company && $company->db_name) {
                $connectionConfig['host']     = $company->db_host;
                $connectionConfig['database'] = $company->db_name;
                $connectionConfig['username'] = $company->db_username;
                $connectionConfig['password'] = $company->db_password;
            }

            // Set dynamic connection
            config(['database.connections.dynamic' => $connectionConfig]);

            // Update subscription_purchase table
            DB::connection('dynamic')
                ->table('subscription_purchases')
                ->where('payment_id', $payment_id)
                ->update([
                    'payment_status' => $status,
                    'is_active' => $isActive,
                    'status' => $packageStatus,
                    'webhook_response' => $webhookResponse
                ]);

            LogHelper::logSuccess("Company DB updated", [
                'payment_id' => $payment_id,
                'status' => $status,
                'is_active' => $isActive,
                'package_status' => $packageStatus
            ], __FUNCTION__, basename(__FILE__), __LINE__, '');

            return true;

        } catch (\Exception $e) {
            LogHelper::logError(
                'Error while updating Company DB',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                json_encode([
                    'payment_id' => $payment_id,
                    'status' => $status
                ])
            );
            return false;
        }
    }
}
