<?php

namespace App\Interfaces;

interface HashServiceInterface
{
    /**
     * Realiza o hash de uma string
     *
     * @param string $value
     * @return string
     */
    public function hash(string $value): string;

    /**
     * Verifica se uma string corresponde a um hash
     *
     * @param string $value
     * @param string $hashedValue
     * @return bool
     */
    public function check(string $value, string $hashedValue): bool;
}
