<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Contracts\BusinessException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class CardNotFoundException extends RuntimeException implements BusinessException
{
    public function __construct()
    {
        parent::__construct('The requested card was not found.', Response::HTTP_NOT_FOUND);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
