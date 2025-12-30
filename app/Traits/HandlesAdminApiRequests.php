<?php

/**
 * @author neosao
 */

namespace App\Traits;
// 
use Illuminate\Support\Facades\Http;
use App\Helpers\LogHelper;
trait HandlesAdminApiRequests
{
    /**
     * Summary of preparePostApiRequestData
     * @param array $data
     * @return array{payload: string, signature: string, timestamp: int}
     */
    protected function prepareSecurePostApiRequestData(array $data): array
    {
        $payload = base64_encode(json_encode($data));
        $timestamp = time();
        $signature = hash_hmac('sha256', $payload . $timestamp, env('ADMIN_API_SHARED_SECRET'));

        return [
            'payload' => $payload,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];
    }

    /**
     * Summary of makePostApiRequest
     * @param string $endpoint
     * @param array $data
     */
    protected function makeSecurePostApiRequest(string $endpoint, array $data)
    {
        $requestData = $this->prepareSecurePostApiRequestData($data);
		
		 $url = env('ADMIN_API_URL') . $endpoint;

         LogHelper::logSuccess('info', 'API => Sending POST request to URL: ' . $url, __FUNCTION__, basename(__FILE__), __LINE__, '');
		
        return Http::timeout(30)->post(env('ADMIN_API_URL') . $endpoint, $requestData);
    }


    protected function makeSecureMultipartPostApiRequestFlexible(string $endpoint, array $data, $files)
    {
        $requestData = $this->prepareSecurePostApiRequestData($data);
        $request = Http::timeout(60);

        // Normalize
        $files = is_array($files) ? $files : [$files];

        foreach ($files as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $request->attach(
                    'files[]',
                    file_get_contents($file),
                    $file->getClientOriginalName()
                );
            }
        }

        return $request->post(env('ADMIN_API_URL') . $endpoint, $requestData);
    }
}
