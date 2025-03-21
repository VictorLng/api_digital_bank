<?php

namespace App\Exceptions;

use Exception;

class InvalidPasswordException extends Exception
{
    public function __construct(string $message = "Senha inválida", int $code = 400, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'invalid_password',
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}
