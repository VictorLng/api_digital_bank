<?php

namespace App\BO;

use App\BO\Interfaces\CustomerAccountInterface;
use App\Resources\CustomerAccountData;
use App\Repositories\CustomerAccountRepository;
use App\Interfaces\AccountNumberGeneratorInterface;

class CustomerAccountBo implements CustomerAccountInterface
{
    protected CustomerAccountRepository $customerAccountRepository;
    protected CustomerAccountData $customerAccountData;
    protected AccountNumberGeneratorInterface $accountNumberGenerator;

    public function __construct(
        CustomerAccountRepository $customerAccountRepository,
        CustomerAccountData $customerAccountData,
        AccountNumberGeneratorInterface $accountNumberGenerator
    ) {
        $this->customerAccountRepository = $customerAccountRepository;
        $this->customerAccountData = $customerAccountData;
        $this->accountNumberGenerator = $accountNumberGenerator;
    }

    public function addFunds($request)
    {

    }

    public function makeWithdrawal($request)
    {

    }

    public function makeTransfer($request)
    {

    }

    public function getBalance($request)
    {

    }

    public function getStatement($request)
    {

    }

    public function getAccountData($request)
    {

    }

    public function getAccountDataByNumber($request)
    {

    }

    /**
     * Cria uma nova conta de cliente com número de conta gerado automaticamente
     *
     * @param mixed $request
     * @return CustomerAccountData
     */
    public function createCustomerAccount($request)
    {
        $accountNumber = $this->accountNumberGenerator->generate();

        $this->customerAccountData->setNumberAccount($accountNumber);

        if (!$this->customerAccountData->getBalance()) {
            $this->customerAccountData->setBalance(0);
        }

        if (!$this->customerAccountData->getStatus()) {
            $this->customerAccountData->setStatus('active');
        }


        return $this->customerAccountData;
    }
}