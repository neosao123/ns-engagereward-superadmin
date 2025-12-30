<?php  

namespace App\Traits;  

use App\Helpers\LogHelper;  
use Illuminate\Support\Facades\Validator;  
use Symfony\Component\HttpFoundation\Response;  

trait ApiSecurityTrait  
{  
    /**  
     * Validate API request security  
     */  
    protected function validateApiRequest($request)  
    {  
        // Validation rules  
        $rules = [  
            'payload' => 'required',  
            'timestamp' => 'required|integer',  
            'signature' => 'required'  
        ];  

        // Custom validation messages  
        $messages = [  
            'payload.required' => "The payload field is required.",  
            'timestamp.required' => "The timestamp field is required.",  
            'timestamp.integer' => "The timestamp must be an integer.",  
            'signature.required' => "The signature field is required.",  
        ];  

        // Validate the request  
        $validator = Validator::make($request->all(), $rules, $messages);  

        if ($validator->fails()) {  
            LogHelper::logError('validation', 'API => Validation error', '', __FUNCTION__, basename(__FILE__), __LINE__, '');  
            return response()->json([  
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,  
                'message' => $validator->errors()->first(),  
                'data' => null  
            ], Response::HTTP_UNPROCESSABLE_ENTITY);  
        }  

        $payload = $request->input('payload');  
        $timestamp = (int) $request->input('timestamp');  
        $signature = $request->input('signature');  

        // Verify signature and timestamp  
        $expectedSignature = hash_hmac('sha256', $payload . $timestamp, env('SUPERADMIN_API_SHARED_SECRET'));  

        if (!hash_equals($expectedSignature, $signature)) {  
            LogHelper::logError('failed', 'API => Invalid signature', '', __FUNCTION__, basename(__FILE__), __LINE__, '');  
            return response()->json([  
                'status' => Response::HTTP_UNAUTHORIZED,  
                'message' => 'Invalid request signature.',  
                'data' => null  
            ], Response::HTTP_UNAUTHORIZED);  
        }  

        if (abs(time() - $timestamp) > 300) {  
            LogHelper::logError('failed', 'API => Expired signature', '', __FUNCTION__, basename(__FILE__), __LINE__, '');  
            return response()->json([  
                'status' => Response::HTTP_UNAUTHORIZED,  
                'message' => 'The request signature has expired.',  
                'data' => null  
            ], Response::HTTP_UNAUTHORIZED);  
        }  

        // Decode and validate payload  
        $data = json_decode(base64_decode($payload), true);  

        if (json_last_error() !== JSON_ERROR_NONE) {  
            LogHelper::logError('failed', 'API => Invalid JSON payload', '', __FUNCTION__, basename(__FILE__), __LINE__, '');  
            return response()->json([  
                'status' => Response::HTTP_BAD_REQUEST,  
                'message' => 'The payload contains invalid JSON.',  
                'data' => null  
            ], Response::HTTP_BAD_REQUEST);  
        }  

        return $data;  
    }  
}  
