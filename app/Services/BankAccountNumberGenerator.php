<?php

namespace App\Services;

use App\Interfaces\AccountNumberGeneratorInterface;
use App\Interfaces\AccountNumberVerifierInterface;

class BankAccountNumberGenerator implements AccountNumberGeneratorInterface
{
    private AccountNumberVerifierInterface $verifier;
    private string $bankCode;

    public function __construct(
        AccountNumberVerifierInterface $verifier,
        string $bankCode = '001'
    ) {
        $this->verifier = $verifier;
        $this->bankCode = $bankCode;
    }

    /**
     * Gera um número de conta único
     *
     * @return string
     */
    public function generate(): string
    {
        $isUnique = false;
        $accountNumber = '';
        $checkDigit = '';

        while (!$isUnique) {
            $dateCode = date('mdY');
            $random = mt_rand(1000, 9999);
            $baseNumber = $this->bankCode . $dateCode . $random;

            $checkDigit = $this->verifier->calculateCheckDigit($baseNumber);
            $accountNumber = $baseNumber . $checkDigit;

            if (!$this->verifier->exists($accountNumber)) {
                $isUnique = true;
            }
        }

        return $this->formatAccountNumber($accountNumber, $checkDigit);
    }

    /**
     * Formata o número da conta para exibição
     *
     * @param string $accountNumber
     * @param string $checkDigit
     * @return string
     */
    private function formatAccountNumber(string $accountNumber, string $checkDigit): string
    {
        return substr($accountNumber, 0, 3) . '.' .
               substr($accountNumber, 3, 6) . '.' .
               substr($accountNumber, 9, 4) . '-' .
               $checkDigit;
    }
}
