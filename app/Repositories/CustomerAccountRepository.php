<?php

namespace App\Repositories;

use App\Models\CustomerAccount;

class CustomerAccountRepository
{
    protected $model;

    public function __construct(CustomerAccount $model)
    {
        $this->model = $model;
    }

    public function register($customerData)
    {
        return $this->model->firstOrCreate([
            'number_account' => $customerData->number_account,
            'user_id' => $customerData->user_id,
        ], [
            'number_account' => $customerData->number_account,
            'agency' => $customerData->agency,
            'type_account' => $customerData->type_account,
            'status' => $customerData->status,
            'user_id' => $customerData->user_id,
            'balance' => 0
        ]);
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
     * Cria uma nova conta de cliente
     *
     * @param array $data
     * @return CustomerAccount
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Busca uma conta pelo ID
     *
     * @param int $id
     * @return CustomerAccount|null
     */
    public function findById(int $id)
    {
        return $this->model->findById($id);
    }

    /**
     * Busca uma conta pelo número da conta
     *
     * @param string $accountNumber
     * @return CustomerAccount|null
     */
    public function findByAccountNumber(string $accountNumber)
    {
        return $this->model->where('number_account', $accountNumber)->first();
    }

    /**
     * Atualiza o saldo de uma conta
     *
     * @param int $id
     * @param float $newBalance
     * @return bool
     */
    public function updateBalance(int $id, float $newBalance): bool
    {
        return $this->model->where('id', $id)->update(['balance' => $newBalance]);
    }
}