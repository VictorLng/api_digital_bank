<?php

namespace App\Services\Auth;

use App\Exceptions\UserNotFoundException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\AuthenticationException;
use App\Interfaces\HashServiceInterface;
use App\Models\User;
use App\BO\CustomerAccountBo;
use App\Repositories\UserRepository;
use App\Resources\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthService
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
        $this->customerAccountBo = $customerAccountBo;
        $this->hashService = $hashService;
    }

    /**
     * Realiza o login do usuário
     *
     * @param object $request
     * @return User
     * @throws UserNotFoundException|InvalidPasswordException
     */
    public function login($request): User
    {
        $user = $this->userRepository->findByEmail($request);
        if (!$user) {
            throw new UserNotFoundException($request->email);
        }

        if (!$this->hashService->check($request->password, $user->password)) {
            throw new InvalidPasswordException();
        }

        $this->setuserToken($user->createToken('UserToken')->accessToken, $user);

        return $user;
    }

    private function setuserToken($token,$user )
    {
        $user->token = $token;
        return $user;
    }

    /**
     * Valida e retorna o usuário autenticado pelo token
     *
     * @param Request $request
     * @return User
     * @throws AuthenticationException
     */
    public function validateAuthenticatedUser(Request $request): User
    {
        $user = $request->user();

        if (!$user) {
            Log::warning('Tentativa de acesso com token inválido ou expirado');
            throw new AuthenticationException("Usuário não autenticado ou token inválido");
        }

        Log::info("Usuário autenticado: {$user->email}");
        return $user;
    }

    /**
     * Realiza o logout do usuário
     *
     * @param Request $request
     * @return bool
     */
    public function logout(Request $request): bool
    {
        try {
            $user = $this->validateAuthenticatedUser($request);
            // Revoga o token atual
            $token = $request->user()->token();
            if ($token) {
                $token->revoke();
                Log::info("Token revogado para o usuário: {$user->email}");
            }

            return true;
        } catch (AuthenticationException $e) {
            Log::error('Erro ao fazer logout: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro desconhecido ao fazer logout: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mapeia os dados do usuário para o objeto UserData
     *
     * @param User $user
     * @param UserData $userData
     * @return UserData
     */
    public function mapUserToUserData(User $user, UserData $userData): array
    {
        return $userData->setName($user->name)
            ->setEmail($user->email)
            ->setCpf($user->cpf)
            ->setPhone($user->phone)
            ->setBirthDate($user->birth_date)
            ->setToken($user->token)
            ->setCustomerAccount($this->customerAccountBo->convertToCustomerAccountData($this->customerAccountBo->findByUserId($user->id)))
            ->toArray();
    }
}
