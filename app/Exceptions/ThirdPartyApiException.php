<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class ThirdPartyApiException extends RuntimeException
{
    /**
     * Construct the exception
     *
     * @param string               $message
     * @param int                  $statusCode HTTP status (0 = no response / connection failed)
     * @param array<string, mixed> $context    Response body etc. for debugging
     * @param Throwable|null        $previous
     *
     * @return void
     */
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get the HTTP status code
     *
     * @return int
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the context
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
