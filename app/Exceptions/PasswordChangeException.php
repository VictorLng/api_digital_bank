<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class PasswordChangeException extends Exception
{
    public function __construct(string $message = "Erro ao alterar senha")
    {
        parent::__construct($message, 500);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
