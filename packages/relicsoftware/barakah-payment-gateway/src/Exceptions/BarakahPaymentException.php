<?php

namespace RelicSoftware\BarakahPayment\Exceptions;

use Exception;

class BarakahPaymentException extends Exception
{
    protected ?array $responseBody = null;

    public function __construct(string $message = '', int $code = 0, ?array $responseBody = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseBody = $responseBody;
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}

