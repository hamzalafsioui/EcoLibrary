<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register exception handling callbacks.
     */
    public function register(): void
    {
        //
    }

    /**
     * Handle unauthenticated users (401)
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }

    /**
     * Render exceptions as JSON for API
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {

            // Validation errors
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => $e->errors()
                ], 422);
            }

            // Model not found
            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Resource not found'
                ], 404);
            }

            // Authorization error
            if ($e instanceof AuthorizationException) {
                return response()->json([
                    'message' => 'Forbidden'
                ], 403);
            }

            // HTTP exceptions (404, 405,...)
            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'HTTP Error'
                ], $e->getStatusCode());
            }

            // Fallback server error
            return response()->json([
                'message' => 'Server Error'
            ], 500);
        }

        return parent::render($request, $e);
    }
}
