<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Contracts\BusinessException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class InvalidTransferException extends RuntimeException implements BusinessException
{
    public function __construct(string $message)
    {
        parent::__construct($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
