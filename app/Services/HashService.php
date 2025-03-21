<?php

namespace App\Services;

use App\Interfaces\HashServiceInterface;
use Illuminate\Support\Facades\Hash;

class HashService implements HashServiceInterface
{
    /**
     * Realiza o hash de uma string
     *
     * @param string $value
     * @return string
     */
    public function hash(string $value): string
    {
        return Hash::make($value);
    }

    /**
     * Verifica se uma string corresponde a um hash
     *
     * @param string $value
     * @param string $hashedValue
     * @return bool
     */
    public function check(string $value, string $hashedValue): bool
    {
        return Hash::check($value, $hashedValue);
    }
}
