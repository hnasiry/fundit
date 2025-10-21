<?php

declare(strict_types=1);

namespace App\Exceptions\Contracts;

interface BusinessException
{
    public function getStatusCode(): int;
}
