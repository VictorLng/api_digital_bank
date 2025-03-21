<?php

namespace App\Services\Auth;

use App\Exceptions\UserNotFoundException;
use App\Mail\ForgotPasswordMail;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Processa a solicitação de recuperação de senha
     */
    public function process($request): bool
    {
        try {

            $token = $this->generateToken();

            $user = $this->findUserByEmail($request->email);

            $expiresInMinutes = 60;

            $this->saveToken($request->email, $token, $expiresInMinutes);

            $this->sendPasswordResetEmail($request->email, $token, $expiresInMinutes);

            return true;
        } catch (UserNotFoundException $e) {
            // Ainda retornamos true para não revelar se o email existe
            Log::info("Tentativa de recuperação para email não existente: {$request->email}");
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao processar recuperação de senha: {$e->getMessage()}");
            return false;
        }
    }

    private function generateToken(): string
    {
        return sprintf('%04d', mt_rand(0, 9999));
    }

    private function findUserByEmail(string $email): User
    {
        $emailObject = (object) ['email' => $email];
        $user = $this->userRepository->findByEmail($emailObject);

        if (!$user) {
            throw new UserNotFoundException($email);
        }

        return $user;
    }

    private function saveToken(string $email, string $token, int $expiresInMinutes): void
    {
        $tokenData = (object) [
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addMinutes($expiresInMinutes)
        ];

        $this->userRepository->saveForgotPasswordToken($tokenData);
    }

    private function sendPasswordResetEmail(string $email, string $token, int $expiresInMinutes): void
    {
        Mail::to($email)->send(new ForgotPasswordMail($token, $expiresInMinutes));
    }
}
