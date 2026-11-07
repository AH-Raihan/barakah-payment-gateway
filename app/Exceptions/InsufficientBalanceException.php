<?php

namespace App\Exceptions;

class InsufficientBalanceException extends \RuntimeException
{
    /**
     * Create a new InsufficientBalanceException instance.
     */
    public function __construct(string $message = 'Insufficient wallet balance.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
