<?php

namespace App\BO\Interfaces;

use App\Resources\CustomerAccountData;

interface CustomerAccountInterface
{
    /**
     * Adiciona fundos a uma conta
     *
     * @param mixed $request
     * @return mixed
     */
    public function addFunds($request);

    /**
     * Realiza um saque de uma conta
     *
     * @param mixed $request
     * @return mixed
     */
    public function makeWithdrawal($request);

    /**
     * Realiza uma transferência entre contas
     *
     * @param mixed $request
     * @return mixed
     */
    public function makeTransfer($request);

    /**
     * Obtém o saldo de uma conta
     *
     * @param mixed $request
     * @return mixed
     */
    public function getBalance($request);

    /**
     * Obtém o extrato de uma conta
     *
     * @param mixed $request
     * @return mixed
     */
    public function getStatement($request);

    /**
     * Obtém os dados de uma conta
     *
     * @param mixed $request
     * @return mixed
     */
    public function getAccountData($request);

    /**
     * Obtém os dados de uma conta pelo número
     *
     * @param mixed $request
     * @return mixed
     */
    public function getAccountDataByNumber($request);

    /**
     * Cria uma nova conta de cliente
     *
     * @param mixed $request
     * @return CustomerAccountData
     */
    public function createCustomerAccount($request): CustomerAccountData;
}