<?php

namespace App\Exceptions;

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
         * Render an exception into an HTTP response.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Throwable  $exception
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function render($request, Throwable $exception)
        {
            // Check if the request is for api/v1/ route
            if ($request->is('api/v1/*')) {
                // Use Symfony HttpException if available, otherwise default to 500
                $status = 500;
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    $status = $exception->getStatusCode();
                }
                $message = $exception->getMessage() ?: 'Server Error';
                return response()->json([
                    'success' => false,
                    'error' => $message,
                ], $status);
            }
            return parent::render($request, $exception);
        }
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
