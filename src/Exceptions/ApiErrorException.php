<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

abstract class ApiErrorException extends ApiException
{
    private array $data;

    public function __construct(array $data, string $message, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
