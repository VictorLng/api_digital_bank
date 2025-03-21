<?php

namespace App\BO;

// Removed unused use directive
use App\Repositories\UserRepository;
use App\Resources\UserData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\BO\Interfaces\UserInterface;
use App\BO\CustomerAccountBo;
use Illuminate\Support\Facades\App;

class UserBo implements UserInterface
{
    protected $userRepository;
    protected $userData;
    protected $customerAccountBo;

    public function __construct(
        UserRepository $userRepository,
        UserData $userData,
        CustomerAccountBo $customerAccountBo
    ) {
        $this->userData = $userData;
        $this->userRepository = $userRepository;
        $this->customerAccountBo = $customerAccountBo;
    }

    public function Register($request): UserData
    {
        try {
            DB::beginTransaction();

            $this->userData->setName($request->name)
                ->setCpf($request->cpf)
                ->setEmail($request->email)
                ->setPassword(Hash::make($request->password))
                ->setRememberToken(Hash::make($request->email))
                ->setCustomerAccount($this->customerAccountBo->createCustomerAccount($request));

            dd($this->userData->toArray());
            $this->userRepository->Register($this->userData->toArray());

            DB::commit();
            return $this->userData;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function Login($request): UserData
    {
        $user = $this->userRepository->Login($request);
        return $this->getUserAcessData($user);
    }

    public function Logout($request): bool
    {
        $this->userRepository->Logout($request);
        return true;
    }

    public function forgotPassword($request): bool
    {
        $user = $this->validateUser($request->email);

        // Gerar token de recuperação
        $token = bin2hex(random_bytes(16));

        // Salvar o token no repositório
        $this->userRepository->savePasswordResetToken($user->id, $token);

        // Enviar email de recuperação
        \Mail::to($user->email)->send(new ForgotPasswordMail($token));

        return true;
    }

    private function validateUser(string $email): App\Models\User
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw new UserNotFoundException('Usuário não encontrado');
        }
        return $user;
    }

    private function validateCurrentPassword(User $user, string $currentPassword): void
    {
        if (!hash::check($currentPassword, $user->password)) {
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
        } catch (DomainException $e) {
            // Log específico para erros de domínio
            \Log::warning('Falha na alteração de senha: ' . $e->getMessage());
            throw $e; // Propagar para tratamento na camada superior
        } catch (\Exception $e) {
            \Log::error('Erro ao alterar senha: ' . $e->getMessage());
            throw new PasswordChangeException('Erro interno ao alterar senha', 0, $e);
        }
    }

    private function getUserAcessData($request)
    {
        $this->userData;
        return $this->userData;
    }
}