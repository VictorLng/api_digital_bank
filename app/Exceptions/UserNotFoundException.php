<?php

namespace App\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    public function __construct(string $message = "Usuário não encontrado", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'user_not_found',
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}
