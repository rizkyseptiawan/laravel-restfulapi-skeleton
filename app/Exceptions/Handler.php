<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e) {
            if (request()->is('api/*')) {
                $statusCode = 500;
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
            return $e;
        });

    }
}
