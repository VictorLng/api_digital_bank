<?php

namespace App\Services\User;

use App\BO\CustomerAccountBo;
use App\Exceptions\DomainException;
use App\Interfaces\HashServiceInterface;
use App\Repositories\UserRepository;
use App\Resources\UserData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRegistrationService
{
    protected UserRepository $userRepository;
    protected HashServiceInterface $hashService;
    protected CustomerAccountBo $customerAccountBo;

    public function __construct(
        UserRepository $userRepository,
        HashServiceInterface $hashService,
        CustomerAccountBo $customerAccountBo
    ) {
        $this->userRepository = $userRepository;
        $this->hashService = $hashService;
        $this->customerAccountBo = $customerAccountBo;
    }

    /**
     * Registra um novo usuário
     *
     * @param object $request
     * @param UserData $userData
     * @return UserData
     * @throws DomainException
     */
    public function register($request, UserData $userData): UserData
    {
        try {
            DB::beginTransaction();

            $userData->setName($request->name)
                ->setCpf($request->cpf)
                ->setBirthDate($request->birth_date)
                ->setPhone($request->phone)
                ->setEmail($request->email)
                ->setPassword($this->hashService->hash($request->password));

            $user = $this->userRepository->Register($userData->toArray());

            if ($user) {
                $userData->setCustomerAccount($this->customerAccountBo->createCustomerAccount($user, $request->bank_account_type));
            }

            DB::commit();
            return $userData;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao registrar usuário: ' . $e->getMessage());
            throw new DomainException('Erro ao registrar usuário: ' . $e->getMessage(). '-'. $e->getFile() . ' - ' . $e->getLine());
        }
    }
}
