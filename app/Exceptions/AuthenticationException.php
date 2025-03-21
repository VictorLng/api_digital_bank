<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AuthenticationException extends Exception
{
    public function __construct(string $message = "Usuário não autenticado ou token inválido")
    {
        parent::__construct($message, 401);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
