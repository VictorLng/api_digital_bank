<?php

namespace App\Repositories;

use App\Models\CustomerAccount;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CustomerAccountRepository
{
    protected $model;
    protected $transactionModel;
    protected $userModel;

    public function __construct(CustomerAccount $model, Transaction $transactionModel, User $userModel)
    {
        $this->model = $model;
        $this->transactionModel = $transactionModel;
        $this->userModel = $userModel;
    }

    /**
     * Registra uma nova conta
     *
     * @param array $data
     * @return CustomerAccount
     */
    public function register(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Verifica se um número de conta já existe
     *
     * @param string $accountNumber
     * @return bool
     */
    public function accountNumberExists(string $accountNumber): bool
    {
        return $this->model->where('number_account', $accountNumber)->exists();
    }

    /**
     * Busca uma conta pelo número
     *
     * @param string $accountNumber
     * @return CustomerAccount|null
     */
    public function findByAccountNumber(string $accountNumber)
    {
        return $this->model->where('number_account', $accountNumber)->first();
    }

    /**
     * Busca uma conta pelo ID
     *
     * @param int $id
     * @return CustomerAccount|null
     */
    public function findById(int $id)
    {
        return $this->model->find($id);
    }

    /**
     * Busca contas pelo ID do usuário
     *
     * @param int $userId
     * @return Collection
     */
    public function findByUserId(int $userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    /**
     * Atualiza o saldo de uma conta
     *
     * @param int $accountId
     * @param float $newBalance
     * @return bool
     */
    public function updateBalance(int $accountId, float $newBalance): bool
    {
        return $this->model->where('id', $accountId)->update(['balance' => $newBalance]);
    }

    /**
     * Cria uma nova transação
     *
     * @param array $data
     * @return Transaction
     */
    public function createTransaction(array $data)
    {
        return $this->transactionModel->create($data);
    }

    /**
     * Obtém as transações de uma conta por período
     *
     * @param int $accountId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return Collection
     */
    public function getTransactions(int $accountId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = $this->transactionModel->where('account_id', $accountId);

        if ($startDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = Carbon::parse($endDate)->endOfDay();
            $query->where('created_at', '<=', $endDate);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Obtém o número da conta pelo ID
     *
     * @param int $accountId
     * @return string|null
     */
    public function getAccountNumberById(int $accountId): ?string
    {
        $account = $this->model->find($accountId);
        return $account ? $account->number_account : null;
    }

    /**
     * Obtém os dados do usuário pelo ID
     *
     * @param int $userId
     * @return User|null
     */
    public function getUserData(int $userId)
    {
        return $this->userModel->find($userId);
    }
}