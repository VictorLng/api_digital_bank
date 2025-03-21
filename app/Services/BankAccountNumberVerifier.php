<?php

namespace App\Services;

use App\Interfaces\AccountNumberVerifierInterface;
use App\Repositories\CustomerAccountRepository;

class BankAccountNumberVerifier implements AccountNumberVerifierInterface
{
    private CustomerAccountRepository $repository;

    public function __construct(CustomerAccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Verifica se um número de conta já existe
     *
     * @param string $accountNumber
     * @return bool
     */
    public function exists(string $accountNumber): bool
    {
        return $this->repository->accountNumberExists($accountNumber);
    }

    /**
     * Calcula o dígito verificador para um número de conta usando algoritmo Módulo 11
     *
     * @param string $baseNumber
     * @return string
     */
    public function calculateCheckDigit(string $baseNumber): string
    {
        $sum = 0;
        $weight = 2;

        $digits = array_reverse(str_split($baseNumber));

        foreach ($digits as $digit) {
            $sum += $this->processDigit((int)$digit, $weight);
            $weight = ($weight >= 9) ? 2 : $weight + 1;
        }

        $modulo = $sum % 11;
        return (string)($modulo < 2 ? 0 : 11 - $modulo);
        }

    /**
     * Processa um dígito de acordo com o algoritmo
     *
     * @param int $digit
     * @param int $weight
     * @return int
     */
    private function processDigit(int $digit, int $weight): int
    {
        $product = $digit * $weight;

        return ($product > 9) ? array_sum(str_split($product)) : $product;
    }
}

