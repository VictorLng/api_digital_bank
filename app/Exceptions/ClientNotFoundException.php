<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ClientNotFoundException extends Exception
{
    public function __construct(string $cpf)
    {
        parent::__construct('Cliente com CPF ' . $cpf . ' não encontrado', 404);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
