<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\ForgotPasswordToken;
use Carbon\Carbon;

class UserRepository
{
    protected User $userModel;
    protected ForgotPasswordToken $forgotPasswordToken;

    public function __construct(User $userModel, ForgotPasswordToken $forgotPasswordToken)
    {
        $this->userModel = $userModel;
        $this->forgotPasswordToken = $forgotPasswordToken;
    }

    public function Register(array $userData): User
    {
        return $this->userModel->firstOrCreate([
            'cpf' => $userData['cpf'],
        ],[
            'name'  => $userData['name'],
            'cpf' => $userData['cpf'],
            'birth_date' => $userData['birth_date'],
            'phone' => $userData['phone'],
            'email' => $userData['email'],
            'password' => $userData['password'],
        ]);
    }

    public function Login($userData): User
    {
        return $this->userModel->where('email', $userData->email)->first();
    }

    public function Logout($userData): User
    {
        return $this->userModel->where('email', $userData->email)->first();
    }

    public function forgotPassword($userData): User
    {
        return $this->userModel->where('email', $userData->email)->first();
    }

    public function passwordChange($userData): bool
    {
        return $this->userModel->update(
                    [
                        'email'    => $userData->email,
                        'password' => $userData->password
                    ],
                    [
                        'password'=> $userData->newPassword
                    ]);
    }

    public function findByEmail($userData): User|null
    {
        return $this->userModel->where('email', $userData->email)->first();
    }

    /**
     * Salva um token para recuperação de senha
     *
     * @param object $userData
     * @return ForgotPasswordToken
     */
    public function saveForgotPasswordToken($userData): ForgotPasswordToken
    {
        // Remove tokens antigos do mesmo email
        $this->forgotPasswordToken->where('email', $userData->email)->delete();

        // Cria novo token
        return $this->forgotPasswordToken->create([
            'email' => $userData->email,
            'token' => $userData->token,
            'created_at' => $userData->created_at,
            'expires_at' => $userData->expires_at,
        ]);
    }

    /**
     * Verifica se um token é válido
     *
     * @param string $email
     * @param string $token
     * @return bool
     */
    public function isValidForgotPasswordToken(string $email, string $token): bool
    {
        $tokenRecord = $this->forgotPasswordToken
            ->where('email', $email)
            ->where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        return $tokenRecord !== null;
    }
}