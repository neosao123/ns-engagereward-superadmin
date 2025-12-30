<?php

/**
 * LogHelper Class
 * 
 * Helper to log error and success messages with structured context.
 * 
 * @author neosao
 */

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class LogHelper
{
    // ----------------------------------------
    // $type values:
    // - exception         → for caught exceptions
    // - success           → for successful operations
    // - validation_error  → for validation failures
    // - condition_failed  → for custom condition failures
    // ----------------------------------------

    /**
     * Log Message For Error or Exception
     *
     * @param string $type        Type of error log (default: 'exception')
     * @param string $message     Custom log message
     * @param mixed  $exception   Exception object or string
     * @param string $function    Function name where error occurred
     * @param string $file        File name
     * @param int    $line        Line number
     * @param string $path        Request path or route
     * @param mixed  $id          (Optional) Related entity ID
     */
    public static function logError(
        $type = "exception",
        $message,
        $exception,
        $function,
        $file,
        $line,
        $path,
        $id = ''
    ) {
        // All request data excluding file fields
        $requestData = request()->except(array_keys(request()->files->all()));

        $data = [
            'type'      => $type,
            'user_id'   => auth()->id(),
            'function'  => $function,
            'file'      => $file,
            'line'      => $line,
            'path'      => $path,
            'exception' => $exception,
            'request'   => $requestData,
        ];

        if (!empty($id)) {
            $data['id'] = $id;
        }

        Log::error($message, $data);
    }

    /**
     * Log Message For Success
     *
     * @param string $type      Type of log (default: 'success')
     * @param string $message   Success message
     * @param string $function  Function name
     * @param string $file      File name
     * @param int    $line      Line number
     * @param string $path      Request path or route
     * @param mixed  $id        (Optional) Related entity ID
     */
    public static function logSuccess(
        $type = "success",
        $message,
        $function,
        $file,
        $line,
        $path,
        $id = ''
    ) {
        // All request data excluding file fields
        $requestData = request()->except(array_keys(request()->files->all()));

        $data = [
            'type'     => $type,
            'user_id'  => auth()->id(),
            'function' => $function,
            'file'     => $file,
            'line'     => $line,
            'path'     => $path,
            'request'  => $requestData,
        ];

        if (!empty($id)) {
            $data['id'] = $id;
        }

        Log::info($message, $data);
    }
}
