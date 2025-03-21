<?php

namespace App\BO;

use App\Repositories\UserRepository;
use App\Resources\UserData;
use App\Models\User;
use App\BO\Interfaces\UserInterface;
use App\BO\CustomerAccountBo;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\DomainException;
use App\Exceptions\PasswordChangeException;
use App\Interfaces\HashServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Log;

class UserBo implements UserInterface
{
    protected UserRepository $userRepository;
    protected UserData $userData;
    protected CustomerAccountBo $customerAccountBo;
    protected HashServiceInterface $hashService;

    public function __construct(
        UserRepository $userRepository,
        UserData $userData,
        CustomerAccountBo $customerAccountBo,
        HashServiceInterface $hashService
    ) {
        $this->userData = $userData;
        $this->userRepository = $userRepository;
        $this->customerAccountBo = $customerAccountBo;
        $this->hashService = $hashService;
    }

    public function Register($request): UserData
    {
        try {
            DB::beginTransaction();

            $this->userData->setName($request->name)
                ->setCpf($request->cpf)
                ->setEmail($request->email)
                ->setPassword($this->hashService->hash($request->password))
                ->setRememberToken($this->hashService->hash($request->email));

            $user = $this->userRepository->Register($this->userData->toArray());

            if ($user) {
                $this->userData->setCustomerAccount($this->customerAccountBo->createCustomerAccount($user));
            }

            DB::commit();
            return $this->userData;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao registrar usuário: ' . $e->getMessage());
            throw new DomainException('Erro ao registrar usuário: ' . $e->getMessage());
        }
    }

    public function Login($request): UserData
    {
        try {
            $user = $this->userRepository->Login($request);

            if (!$user) {
                throw new UserNotFoundException('Usuário não encontrado');
            }

            if (!$this->hashService->check($request->password, $user->password)) {
                throw new InvalidPasswordException('Senha incorreta');
            }

            return $this->getUserAccessData($user);
        } catch (\Exception $e) {
            Log::error('Erro ao fazer login: ' . $e->getMessage());
            throw $e;
        }
    }

    public function Logout($request): bool
    {
        try {
            $this->userRepository->Logout($request);
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao fazer logout: ' . $e->getMessage());
            return false;
        }
    }

    public function forgotPassword($request): bool
    {
        try {
            // Gerar token numérico de 4 dígitos
            $token = sprintf('%04d', mt_rand(0, 9999));

            $user = $this->validateUser($request->email);

            $expiresInMinutes = 60;
            $tokenData = (object) [
                'email' => $request->email,
                'token' => $token,
                'created_at' => now(),
                'expires_at' => now()->addMinutes($expiresInMinutes)
            ];

            $this->userRepository->saveForgotPasswordToken($tokenData);

            // Enviar email com o token
            Mail::to($request->email)->send(new ForgotPasswordMail($token, $expiresInMinutes));

            return true;
        } catch (UserNotFoundException $e) {
            // Ainda retornamos true para não revelar se o email existe
            Log::info('Tentativa de recuperação para email não existente: ' . $request->email);
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao processar recuperação de senha: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida se um usuário existe pelo email
     *
     * @param string $email
     * @return User
     * @throws UserNotFoundException
     */
    private function validateUser(string $email): User
    {
        // Criando objeto para compatibilidade com o método do repositório
        $emailObject = (object) ['email' => $email];
        $user = $this->userRepository->findByEmail($emailObject);

        if (!$user) {
            throw new UserNotFoundException('Usuário não encontrado');
        }

        return $user;
    }

    /**
     * Valida se a senha atual está correta
     *
     * @param User $user
     * @param string $currentPassword
     * @return void
     * @throws InvalidPasswordException
     */
    private function validateCurrentPassword(User $user, string $currentPassword): void
    {
        if (!$this->hashService->check($currentPassword, $user->password)) {
            throw new InvalidPasswordException('Senha atual incorreta');
        }
    }

    public function passwordChange($request): bool
    {
        try {
            $user = $this->validateUser($request->email);
            $this->validateCurrentPassword($user, $request->current_password);

            $this->userData->setEmail($request->email)
                ->setPassword($user->password)
                ->setNewPassword($this->hashService->hash($request->new_password));

            return $this->userRepository->passwordChange($this->userData);
        } catch (UserNotFoundException | InvalidPasswordException $e) {
            // Erros de domínio específicos
            Log::warning('Falha na alteração de senha: ' . $e->getMessage());
            throw $e; // Propagar para tratamento na camada superior
        } catch (\Exception $e) {
            Log::error('Erro ao alterar senha: ' . $e->getMessage());
            throw new PasswordChangeException('Erro interno ao alterar senha', 500, $e);
        }
    }

    /**
     * Obtém os dados de acesso do usuário
     *
     * @param User $user
     * @return UserData
     */
    private function getUserAccessData(User $user): UserData
    {
        $this->userData->setName($user->name)
            ->setEmail($user->email)
            ->setCpf($user->cpf);

        return $this->userData;
    }
}