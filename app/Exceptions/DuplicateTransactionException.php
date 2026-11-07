<?php

namespace App\Exceptions;

class DuplicateTransactionException extends \RuntimeException
{
    /**
     * Create a new DuplicateTransactionException instance.
     */
    public function __construct(string $message = 'Duplicate transaction detected.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
