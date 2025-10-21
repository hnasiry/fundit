<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Contracts\BusinessException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class Handler extends ExceptionHandler
{
    /**
     * @var array<class-string<Throwable>, callable>
     */
    protected $dontReport = [];

    /**
     * @var list<string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (BusinessException $exception, Request $request): JsonResponse {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        });
    }
}
