<?php

namespace App\Interfaces\Mail;

interface PasswordResetMailInterface
{
    /**
     * Criar uma nova instância de mensagem
     *
     * @param string $token
     * @return void
     */
    public function __construct(string $token);
}
