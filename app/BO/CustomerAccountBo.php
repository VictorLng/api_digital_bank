<?php

namespace App\BO;

use App\BO\Interfaces\CustomerAccountInterface;
use App\Resources\CustomerAccountData;
use App\Repositories\CustomerAccountRepository;
use App\Interfaces\AccountNumberGeneratorInterface;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransactionException;
use App\Models\CustomerAccount;
use Illuminate\Support\Facades\Log;

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
        try {
            $account = $this->findAccount($request->account_number);

            // Lógica para adicionar fundos
            // ...

            return $account;
        } catch (AccountNotFoundException $e) {
            Log::error('Erro ao adicionar fundos: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro desconhecido ao adicionar fundos: ' . $e->getMessage());
            throw new InvalidTransactionException("Erro ao processar depósito: " . $e->getMessage());
        }
    }

    public function makeWithdrawal($request)
    {
        try {
            $account = $this->findAccount($request->account_number);

            if ($account->getBalance() < $request->amount) {
                throw new InsufficientFundsException();
            }

            // Lógica para retirar fundos
            // ...

            return $account;
        } catch (AccountNotFoundException | InsufficientFundsException $e) {
            Log::error('Erro ao realizar saque: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro desconhecido ao realizar saque: ' . $e->getMessage());
            throw new InvalidTransactionException("Erro ao processar saque: " . $e->getMessage());
        }
    }

    public function makeTransfer($request)
    {
        try {
            $sourceAccount = $this->findAccount($request->source_account_number);
            $targetAccount = $this->findAccount($request->target_account_number);

            if ($sourceAccount->getBalance() < $request->amount) {
                throw new InsufficientFundsException();
            }

            // Lógica para transferir fundos
            // ...

            return $sourceAccount;
        } catch (AccountNotFoundException | InsufficientFundsException $e) {
            Log::error('Erro ao realizar transferência: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro desconhecido ao realizar transferência: ' . $e->getMessage());
            throw new InvalidTransactionException("Erro ao processar transferência: " . $e->getMessage());
        }
    }

    public function getBalance($request)
    {
        try {
            $account = $this->findAccount($request->account_number);
            return $account->getBalance();
        } catch (AccountNotFoundException $e) {
            Log::error('Erro ao obter saldo: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getStatement($request)
    {

    }

    public function getAccountData($request)
    {
        try {
            return $this->findAccount($request->account_number);
        } catch (AccountNotFoundException $e) {
            Log::error('Erro ao obter dados da conta: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAccountDataByNumber($request)
    {
        try {
            return $this->findAccount($request->account_number);
        } catch (AccountNotFoundException $e) {
            Log::error('Erro ao obter dados da conta por número: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Encontra uma conta pelo número
     *
     * @param string $accountNumber
     * @return
     * @throws AccountNotFoundException
     */
    private function findAccount(string $accountNumber): CustomerAccount
    {
        $account = $this->customerAccountRepository->findByAccountNumber($accountNumber);

        if (!$account) {
            throw new AccountNotFoundException($accountNumber);
        }

        return $account;
    }
    public function findByUserId($userId)
    {
        $account = $this->customerAccountRepository->findByUserId($userId);
        return $this->mountCustomerAccountData($account);
    }

    public function mountCustomerAccountData($request)
    {
        $this->customerAccountData->setNumberAccount($request->number_account)
            ->setIdUser($request->id_user)
            ->setTypeAccount($request->type_account)
            ->setBalance($request->balance)
            ->setStatus($request->status)
            ->setAgency($request->agency);

        return $this->customerAccountData;
    }
    /**
     * Cria uma nova conta de cliente com número de conta gerado automaticamente
     *
     * @param mixed $request
     * @return CustomerAccountData
     */
    public function createCustomerAccount($request, $typeAccount): CustomerAccountData
    {
        $accountNumber = $this->accountNumberGenerator->generate();

        $this->customerAccountData->setNumberAccount($accountNumber)
        ->setIdUser($request->id)
        ->setTypeAccount($typeAccount);

        if (!$this->customerAccountData->getBalance()) {
            $this->customerAccountData->setBalance(0);
        }

        if (!$this->customerAccountData->getStatus()) {
            $this->customerAccountData->setStatus('active');
        }

        if (!$this->customerAccountData->getAgency()) {
            $this->customerAccountData->setAgency('0001');
        }

        $this->registerCustomerAccount($this->customerAccountData->toArray());

        return $this->customerAccountData;
    }

    /**
     * Registra uma nova conta de cliente
     *
     * @param CustomerAccountData $customerAccountData
     * @return void
     */
    public function registerCustomerAccount(array $customerAccountData)
    {
        $this->customerAccountRepository->register($customerAccountData);
    }
}