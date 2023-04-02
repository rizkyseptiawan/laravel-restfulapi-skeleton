<?php

namespace App\Traits;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

trait ResponseApi
{
    /**
     * HTTP Status Code Message List for client side (message is not for developer, example: 200 is Successfully processed)
     *
     * @var array
     */
    protected $httpStatusCodeMessage = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
    ];

    /**
     * Generate API Response
     *
     * @param $message string Message to be displayed
     * @param array|string|LengthAwarePaginator|Model $data array Data to be returned
     * @param $statusCode int HTTP Status Code
     * @param $identityResponseCode int Identity Response Code for redirection or other purposes
     * @param $additionalResponse array Additional response to be returned
     * @return JsonResponse JSON Response
     */
    public function generateApiResponse(string $message = 'success', array|string|LengthAwarePaginator|Model $data = [], int $statusCode = 200, int $identityResponseCode = 1000, array $additionalResponse = []): JsonResponse
    {
        $status = true;
        if($statusCode >= 400){
            $status = false;
        }
        if($message === 'success'){
            $message = $this->httpStatusCodeMessage[$statusCode];
        }
        $responseStatusCode = $identityResponseCode + $statusCode;
        $response = [
            'code' => $responseStatusCode,
            'status' => $status,
            'message' => $message,
            'data' => [],
        ];
        if(!empty($data)){
            $response['data'] = $data;
        }
        if(!empty($additionalResponse)){
            $response = $response + $additionalResponse;
        }
        return response()->json($response, $statusCode);
    }


    /**
     * Generate API Response from Exception
     *
     * @param $e Throwable Exception
     * @param $statusCode int HTTP Status Code
     * @return JsonResponse JSON Response
     */
    public function generateApiFromException(Throwable $e, int $statusCode = 500): JsonResponse
    {
        if ($e instanceof AuthenticationException) {
            $statusCode = 401;
        }
        if (method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        } elseif (isset($e->status)) {
            $statusCode = $e->status;
        }
        $errorData = method_exists($e, 'errors') ? $e->errors() : null;
        $response = [
            'code' => (1000 + $statusCode),
            'status' => false,
            'message' => $e->getMessage(),
            'errors' => [],
        ];
        if (!is_null($errorData)) {
            $response['errors'] = $errorData;
        }
        return response()->json($response, $statusCode);
    }

}
