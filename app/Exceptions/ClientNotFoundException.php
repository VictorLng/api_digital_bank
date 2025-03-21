<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Throwable;

class ClientNotFoundException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous )
    {
        parent::__construct($message, $code, $previous);
    }
    public function render(Request $request)
    {
        return response()->json(['message' => $this->getMessage()], $this->getCode());
    }
}
