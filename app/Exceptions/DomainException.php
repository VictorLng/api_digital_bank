<?php

namespace App\Exceptions;

use Exception;

class DomainException extends Exception
{
    public function __construct(string $message = "Erro de domínio", int $code = 422, \Throwable $previous)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'domain_error',
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}
