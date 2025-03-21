<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AccountNotFoundException extends Exception
{
    public function __construct(string $accountNumber)
    {
        $message = $accountNumber ? "Conta com número $accountNumber não encontrada" : "Conta não encontrada";
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
