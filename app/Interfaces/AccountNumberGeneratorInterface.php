<?php

namespace App\Interfaces;

interface AccountNumberGeneratorInterface
{
    /**
     * Gera um número de conta único
     *
     * @return string
     */
    public function generate(): string;
}
