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



use App\Models\Template;
use App\Traits\ApiSecurityTrait;
use Symfony\Component\HttpFoundation\Response;

class TemplateController extends Controller
{
    use ApiSecurityTrait;

    /**
     * Get template details API
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_template_details(Request $request)
    {
        try {
            $data = $this->validateApiRequest($request);
            if ($data instanceof \Illuminate\Http\JsonResponse) {
                return $data;
            }

            if (!isset($data['template_id'])) {
                return response()->json([
                    'status'  => Response::HTTP_BAD_REQUEST,
                    'message' => 'Template ID is required in payload.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $template = Template::where('id', $data['template_id'])->where('is_delete', 0)->first();

            if (!$template) {
                return response()->json([
                    'status'  => Response::HTTP_NOT_FOUND,
                    'message' => 'Template not found.'
                ], Response::HTTP_NOT_FOUND);
            }

            LogHelper::logSuccess('success', 'API => Template details fetched successfully.', __FUNCTION__, basename(__FILE__), __LINE__, "");

            return response()->json([
                'status'  => Response::HTTP_OK,
                'message' => 'Template details fetched successfully.',
                'data'    => [
                    'id'          => $template->id,
                    'title'       => $template->title,
                    'subtitle'    => $template->subtitle,
                    'description' => $template->description,
                    'is_active'   => $template->is_active,
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $exception) {
            LogHelper::logError(
                'exception',
                'Failed to fetch template details.',
                $exception->getMessage(),
                __FUNCTION__,
                basename(__FILE__),
                __LINE__,
                $request->path(),
                ''
            );

            return response()->json([
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred while fetching template details.',
                'error'   => config('app.debug') ? $exception->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}