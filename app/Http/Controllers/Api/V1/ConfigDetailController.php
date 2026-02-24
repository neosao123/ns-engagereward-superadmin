<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\LogHelper;

use App\Models\SocialMedia\FacebookSetting;
use App\Models\SocialMedia\InstagramSetting;

class ConfigDetailController extends Controller
{
    // return config details
    public function config_details()
    {
        try {
            $models = [
                'facebook'  => FacebookSetting::class,
                'instagram' => InstagramSetting::class,
            ];

            $data = [];

            foreach ($models as $key => $model) {
                $record = $model::first();

                if ($record) {
                    $recordArray = $record->toArray();
                    
                    // Decrypt app_id and app_secret if they exist
                    if (isset($recordArray['app_id'])) {
                        $recordArray['app_id'] = decryptData($record->app_id);
                    }
                    if (isset($recordArray['app_secret'])) {
                        $recordArray['app_secret'] = decryptData($record->app_secret);
                    }

                    $data[$key] = $recordArray;
                } else {
                    $data[$key] = null;
                }
            }

            return response()->json([
                'status'  => Response::HTTP_OK,
                'message' => 'Config details fetched successfully.',
                'data'    => $data
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            LogHelper::logError(
                'An error occurred while getting config details',
                $e->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                __FILE__,
                ""
            );

            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => __('api.server_error'),
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
