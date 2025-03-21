<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Token de redefinição de senha
     *
     * @var string
     */
    protected $token;

    /**
     * Tempo de expiração em minutos
     *
     * @var int
     */
    protected $expiresInMinutes;

    /**
     * Criar uma nova instância de mensagem
     *
     * @param string $token
     * @param int $expiresInMinutes
     * @return void
     */
    public function __construct(string $token, int $expiresInMinutes = 30)
    {
        $this->token = $token;
        $this->expiresInMinutes = $expiresInMinutes;
    }

    /**
     * Obter o envelope da mensagem
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Seu código de recuperação de senha - Digital Bank',
        );
    }

    /**
     * Obter o conteúdo da mensagem
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.forgot-password',
            with: [
                'token' => $this->token,
                'expiresIn' => $this->expiresInMinutes
            ],
        );
    }

    /**
     * Obter os anexos da mensagem
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
