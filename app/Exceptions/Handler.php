<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
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
    }

    /**
     * Customize JSON response for API exceptions.
     */
    public function render($request, Throwable $exception)
    {
        // استجابة مخصصة عند فقدان المصادقة (JWT Token غير صالح أو مفقود)
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Unauthorized - Invalid token or missing credentials",
                "status_code" => 401
            ], 401);
        }

        // استجابة عند عدم العثور على المورد (ModelNotFoundException)
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Resource not found",
                "status_code" => 404
            ], 404);
        }

        // استجابة عند عدم العثور على رابط (404)
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Route not found",
                "status_code" => 404
            ], 404);
        }

        // استجابة عند حدوث خطأ في التوكن (JWT)
        if ($exception instanceof JWTException) {
            return response()->json([
                "status" => false,
                "data" => [],
                "message" => "Invalid token",
                "status_code" => 401
            ], 401);
        }

        // استجابة مخصصة للأخطاء غير المتوقعة
        return response()->json([
            "status" => false,
            "data" => [],
            "message" => "Server error: " . $exception->getMessage(),
            "status_code" => 500
        ], 500);
    }
}
