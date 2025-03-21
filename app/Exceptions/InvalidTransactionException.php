<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InvalidTransactionException extends Exception
{
    public function __construct(string $message = "Transação inválida")
    {
        parent::__construct($message, 400);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
