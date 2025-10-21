<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Contracts\BusinessException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class InsufficientFundsException extends RuntimeException implements BusinessException
{
    public function __construct()
    {
        parent::__construct('The source card does not have enough balance.', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
