<?php

namespace App\Exceptions;

use Exception;

class PasswordChangeException extends Exception
{
    public function __construct(string $message = "Erro ao alterar senha", int $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'password_change_error',
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}
