<?php

namespace App\Traits;

use App\Helpers\LogHelper;
// 
use Symfony\Component\HttpFoundation\Response;
// 
use Illuminate\Http\Client\RequestException;

trait HandlesApiResponses
{
    protected function validationError(string $message)
    {
        return response()->json([
            'status'  => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => $message
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function notFound(string $message)
    {
        return response()->json([
            'status'  => Response::HTTP_NOT_FOUND,
            'message' => $message
        ], Response::HTTP_NOT_FOUND);
    }

    protected function unauthorized(string $message)
    {
        return response()->json([
            'status'  => Response::HTTP_UNAUTHORIZED,
            'message' => $message
        ], Response::HTTP_UNAUTHORIZED);
    }


    protected function forbidden(string $message)
    {
        return response()->json([
            'status' => Response::HTTP_FORBIDDEN,
            'message' => $message,
        ], Response::HTTP_FORBIDDEN);
    }



    protected function handleClientException(RequestException $exception)
    {
        $response = $exception->response;
        if ($response->json()) {
            return $response->json();
        } else {
            return response()->json([
                'status' => $response->status(),
                'message' => __('api.exception_message'),
                'error' => $response->body()
            ], $response->status());
        }
    }

    protected function handleGeneralException(\Exception $exception)
    {
        return response()->json([
            'status'  => Response::HTTP_BAD_REQUEST,
            'message' => __('api.exception_message'),
            'error'   => $exception->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }
}
