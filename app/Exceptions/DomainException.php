<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class DomainException extends Exception
{
    public function __construct(string $message = "Erro de domínio")
    {
        parent::__construct($message, 422);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
