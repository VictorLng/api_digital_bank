<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UserNotFoundException extends Exception
{
    public function __construct(string $email)
    {
        $message = $email ? "Usuário com email $email não encontrado" : "Usuário não encontrado";
        parent::__construct($message, 404);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
