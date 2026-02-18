<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->wantsJson()) {
            return $this->renderJsonResponse($e);
        }

        return parent::render($request, $e);
    }

    protected function renderJsonResponse(Throwable $e): JsonResponse
    {
        $statusCode = $this->getStatusCode($e);
        $message = $this->getMessage($e);
        $errors = $this->getErrors($e);

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    protected function getStatusCode(Throwable $e): int
    {
        if (method_exists($e, 'getStatusCode')) {
            return $e->getStatusCode() ?? 500;
        }

        return 500;
    }

    protected function getMessage(Throwable $e): string
    {
        if (method_exists($e, 'getMessage')) {
            return $e->getMessage();
        }

        return 'An error occurred';
    }

    protected function getErrors(Throwable $e): ?array
    {
        if (method_exists($e, 'errors')) {
            return $e->errors();
        }

        return null;
    }
}
