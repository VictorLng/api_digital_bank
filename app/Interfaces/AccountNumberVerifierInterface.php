<?php

namespace App\Interfaces;

interface AccountNumberVerifierInterface
{
    /**
     * Verifica se um número de conta já existe
     *
     * @param string $accountNumber
     * @return bool
     */
    public function exists(string $accountNumber): bool;

    /**
     * Calcula o dígito verificador para um número de conta
     *
     * @param string $baseNumber
     * @return string
     */
    public function calculateCheckDigit(string $baseNumber): string;
}
