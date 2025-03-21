<?php

namespace App\Services\Auth;

use App\Exceptions\PasswordChangeException;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\InvalidPasswordException;
use App\Interfaces\HashServiceInterface;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Resources\UserData;
use Illuminate\Support\Facades\Log;

class PasswordService
{
    protected UserRepository $userRepository;
    protected HashServiceInterface $hashService;
    protected ForgotPasswordService $forgotPasswordService;

    public function __construct(
        UserRepository $userRepository,
        HashServiceInterface $hashService,
        ForgotPasswordService $forgotPasswordService
    ) {
        $this->userRepository = $userRepository;
        $this->hashService = $hashService;
        $this->forgotPasswordService = $forgotPasswordService;
    }

    /**
     * Inicia o processo de recuperação de senha
     *
     * @param object $request
     * @return bool
     */
    public function forgotPassword($request): bool
    {
        return $this->forgotPasswordService->process($request);
    }

    /**
     * Altera a senha do usuário
     *
     * @param object $request
     * @param UserData $userData
     * @return bool
     * @throws UserNotFoundException|InvalidPasswordException|PasswordChangeException
     */
    public function changePassword($request, UserData $userData): bool
    {
        try {
            $user = $this->findUserByEmail($request->email);
            $this->validateCurrentPassword($user, $request->current_password);

            $userData->setEmail($request->email)
                ->setPassword($user->password)
                ->setNewPassword($this->hashService->hash($request->new_password));

            return $this->userRepository->passwordChange($userData);
        } catch (UserNotFoundException | InvalidPasswordException $e) {
            // Erros de domínio específicos
            Log::warning('Falha na alteração de senha: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro ao alterar senha: ' . $e->getMessage());
            throw new PasswordChangeException();
        }
    }

    /**
     * Encontra um usuário pelo email
     *
     * @param string $email
     * @return User
     * @throws UserNotFoundException
     */
    private function findUserByEmail(string $email): User
    {
        $emailObject = (object) ['email' => $email];
        $user = $this->userRepository->findByEmail($emailObject);

        if (!$user) {
            throw new UserNotFoundException($email);
        }

        return $user;
    }

    /**
     * Valida se a senha atual está correta
     *
     * @param User $user
     * @param string $currentPassword
     * @throws InvalidPasswordException
     */
    private function validateCurrentPassword(User $user, string $currentPassword): void
    {
        if (!$this->hashService->check($currentPassword, $user->password)) {
            throw new InvalidPasswordException();
        }
    }
}
