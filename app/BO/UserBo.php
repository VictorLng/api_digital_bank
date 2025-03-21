<?php

namespace App\BO;

use App\BO\Interfaces\UserInterface;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\DomainException;
use App\Exceptions\PasswordChangeException;
use App\Resources\UserData;
use App\Services\Auth\AuthService;
use App\Services\Auth\PasswordService;
use App\Services\User\UserRegistrationService;
use Illuminate\Support\Facades\Log;

class UserBo implements UserInterface
{
    protected UserData $userData;
    protected AuthService $authService;
    protected PasswordService $passwordService;
    protected UserRegistrationService $userRegistrationService;

    public function __construct(
        UserData $userData,
        AuthService $authService,
        PasswordService $passwordService,
        UserRegistrationService $userRegistrationService
    ) {
        $this->userData = $userData;
        $this->authService = $authService;
        $this->passwordService = $passwordService;
        $this->userRegistrationService = $userRegistrationService;
    }

    /**
     * Registra um novo usuário
     *
     * @param mixed $request
     * @return UserData
     * @throws DomainException
     */
    public function Register($request): UserData
    {
        return $this->userRegistrationService->register($request, $this->userData);
    }

    /**
     * Realiza o login do usuário
     *
     * @param mixed $request
     * @return UserData
     * @throws UserNotFoundException|InvalidPasswordException
     */
    public function Login($request): array
    {
        try {
            $user = $this->authService->login($request);
            return $this->authService->mapUserToUserData($user, $this->userData);
        } catch (\Exception $e) {
            Log::error('Erro ao fazer login: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Realiza o logout do usuário
     *
     * @param mixed $request
     * @return bool
     */
    public function Logout($request): bool
    {
        return $this->authService->logout($request);
    }

    /**
     * Inicia o processo de recuperação de senha
     *
     * @param mixed $request
     * @return bool
     */
    public function forgotPassword($request): bool
    {
        return $this->passwordService->forgotPassword($request);
    }

    /**
     * Altera a senha do usuário
     *
     * @param mixed $request
     * @return bool
     * @throws UserNotFoundException|InvalidPasswordException|PasswordChangeException
     */
    public function passwordChange($request): bool
    {
        return $this->passwordService->changePassword($request, $this->userData);
    }
}