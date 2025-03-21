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
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
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

    /**
     * Adiciona fundos a uma conta
     *
     * @param mixed $request
     * @return CustomerAccountData
     * @throws AccountNotFoundException|InvalidTransactionException
     */
    public function addFunds($request)
    {
        try {
            DB::beginTransaction();

            // Validação de valores negativos
            if ($request->amount <= 0) {
                throw new InvalidTransactionException("O valor do depósito deve ser maior que zero");
            }

            $account = $this->findAccount($request->account_number);
            $currentBalance = $account->balance;
            $newBalance = $currentBalance + $request->amount;

            // Atualiza o saldo
            $this->customerAccountRepository->updateBalance($account->id, $newBalance);

            // Registra a transação
            $transactionData = [
                'account_id' => $account->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Depósito',
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance
            ];
            $this->customerAccountRepository->createTransaction($transactionData);

            $account->balance = $newBalance;
            DB::commit();

            return $this->mountCustomerAccountData($account);
        } catch (AccountNotFoundException $e) {
            DB::rollBack();
            Log::error('Erro ao adicionar fundos: ' . $e->getMessage());
            throw $e;
        } catch (InvalidTransactionException $e) {
            DB::rollBack();
            Log::error('Transação inválida ao adicionar fundos: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro desconhecido ao adicionar fundos: ' . $e->getMessage());
            throw new InvalidTransactionException("Erro ao processar depósito: " . $e->getMessage());
        }
    }

    /**
     * Realiza um saque de uma conta
     *
     * @param mixed $request
     * @return CustomerAccountData
     * @throws AccountNotFoundException|InsufficientFundsException|InvalidTransactionException
     */
    public function makeWithdrawal($request)
    {
        try {
            DB::beginTransaction();

            // Validação de valores negativos
            if ($request->amount <= 0) {
                throw new InvalidTransactionException("O valor do saque deve ser maior que zero");
            }

            $account = $this->findAccount($request->account_number);
            $currentBalance = $account->balance;

            if ($currentBalance < $request->amount) {
                throw new InsufficientFundsException();
            }

            $newBalance = $currentBalance - $request->amount;

            // Atualiza o saldo
            $this->customerAccountRepository->updateBalance($account->id, $newBalance);

            // Registra a transação
            $transactionData = [
                'account_id' => $account->id,
                'type' => 'withdrawal',
                'amount' => -$request->amount,
                'description' => $request->description ?? 'Saque',
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance
            ];
            $this->customerAccountRepository->createTransaction($transactionData);

            $account->balance = $newBalance;
            DB::commit();

            return $this->mountCustomerAccountData($account);
        } catch (AccountNotFoundException | InsufficientFundsException $e) {
            DB::rollBack();
            Log::error('Erro ao realizar saque: ' . $e->getMessage());
            throw $e;
        } catch (InvalidTransactionException $e) {
            DB::rollBack();
            Log::error('Transação inválida ao realizar saque: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro desconhecido ao realizar saque: ' . $e->getMessage());
            throw new InvalidTransactionException("Erro ao processar saque: " . $e->getMessage());
        }
    }

    /**
     * Realiza uma transferência entre contas
     *
     * @param mixed $request
     * @return array
     * @throws AccountNotFoundException|InsufficientFundsException|InvalidTransactionException
     */
    public function makeTransfer($request)
    {
        try {
            DB::beginTransaction();

            // Validação de valores negativos
            if ($request->amount <= 0) {
                throw new InvalidTransactionException("O valor da transferência deve ser maior que zero");
            }

            $sourceAccount = $this->findAccount($request->source_account_number);
            $targetAccount = $this->findAccount($request->target_account_number);

            // Impedir transferência para a mesma conta
            if ($sourceAccount->id === $targetAccount->id) {
                throw new InvalidTransactionException("Não é possível transferir para a mesma conta");
            }

            $sourceCurrentBalance = $sourceAccount->balance;

            if ($sourceCurrentBalance < $request->amount) {
                throw new InsufficientFundsException();
            }

            $targetCurrentBalance = $targetAccount->balance;

            $sourceNewBalance = $sourceCurrentBalance - $request->amount;
            $targetNewBalance = $targetCurrentBalance + $request->amount;

            // Atualiza os saldos
            $this->customerAccountRepository->updateBalance($sourceAccount->id, $sourceNewBalance);
            $this->customerAccountRepository->updateBalance($targetAccount->id, $targetNewBalance);

            // Registra as transações
            $transferId = uniqid('txn_');

            // Transação de débito na conta de origem
            $sourceTransactionData = [
                'account_id' => $sourceAccount->id,
                'type' => 'transfer_out',
                'amount' => -$request->amount,
                'description' => $request->description ?? 'Transferência enviada',
                'reference_id' => $transferId,
                'related_account_id' => $targetAccount->id,
                'balance_before' => $sourceCurrentBalance,
                'balance_after' => $sourceNewBalance
            ];
            $this->customerAccountRepository->createTransaction($sourceTransactionData);

            // Transação de crédito na conta de destino
            $targetTransactionData = [
                'account_id' => $targetAccount->id,
                'type' => 'transfer_in',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Transferência recebida',
                'reference_id' => $transferId,
                'related_account_id' => $sourceAccount->id,
                'balance_before' => $targetCurrentBalance,
                'balance_after' => $targetNewBalance
            ];
            $this->customerAccountRepository->createTransaction($targetTransactionData);

            $sourceAccount->balance = $sourceNewBalance;
            $targetAccount->balance = $targetNewBalance;

            DB::commit();

            return [
                'source_account' => $this->mountCustomerAccountData($sourceAccount),
                'target_account' => $this->mountCustomerAccountData($targetAccount),
                'amount' => $request->amount,
                'transaction_id' => $transferId
            ];
        } catch (AccountNotFoundException | InsufficientFundsException $e) {
            DB::rollBack();
            Log::error('Erro ao realizar transferência: ' . $e->getMessage());
            throw $e;
        } catch (InvalidTransactionException $e) {
            DB::rollBack();
            Log::error('Transação inválida ao realizar transferência: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro desconhecido ao realizar transferência: ' . $e->getMessage());
            throw new InvalidTransactionException("Erro ao processar transferência: " . $e->getMessage());
        }
    }

    /**
     * Obtém o saldo de uma conta
     *
     * @param mixed $request
     * @return array
     * @throws AccountNotFoundException
     */
    public function getBalance($request)
    {
        try {
            $account = $this->findAccount($request->account_number);
            return [
                'account_number' => $account->number_account,
                'balance' => $account->balance,
                'last_update' => $account->updated_at
            ];
        } catch (AccountNotFoundException $e) {
            Log::error('Erro ao obter saldo: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtém o extrato de uma conta
     *
     * @param mixed $request
     * @return array
     * @throws AccountNotFoundException
     */
    public function getStatement($request)
    {
        try {
            $account = $this->findAccount($request->account_number);

            $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
            $endDate = $request->end_date ?? now()->format('Y-m-d');

            $transactions = $this->customerAccountRepository->getTransactions(
                $account->id,
                $startDate,
                $endDate
            );

            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'balance_after' => $transaction->balance_after,
                    'reference_id' => $transaction->reference_id,
                    'related_account' => $transaction->related_account_id ?
                        $this->customerAccountRepository->getAccountNumberById($transaction->related_account_id) : null
                ];
            });

            return [
                'account_number' => $account->number_account,
                'current_balance' => $account->balance,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'transactions' => $formattedTransactions
            ];
        } catch (AccountNotFoundException $e) {
            Log::error('Erro ao obter extrato: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro desconhecido ao obter extrato: ' . $e->getMessage());
            throw new \Exception("Erro ao obter extrato: " . $e->getMessage());
        }
    }

    /**
     * Obtém os dados de uma conta
     *
     * @param mixed $request
     * @return array
     * @throws AccountNotFoundException
     */
    public function getAccountData($request)
    {
        try {
            $account = $this->findAccount($request->account_number);
            $userData = $this->customerAccountRepository->getUserData($account->user_id);

            return [
                'account' => [
                    'id' => $account->id,
                    'number_account' => $account->number_account,
                    'agency' => $account->agency,
                    'type_account' => $account->type_account,
                    'balance' => $account->balance,
                    'status' => $account->status,
                    'created_at' => $account->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $account->updated_at->format('Y-m-d H:i:s')
                ],
                'user' => $userData ? [
                    'id' => $userData->id,
                    'name' => $userData->name,
                    'email' => $userData->email,
                    'cpf' => $userData->cpf
                ] : null
            ];
        } catch (AccountNotFoundException $e) {
            Log::error('Erro ao obter dados da conta: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro desconhecido ao obter dados da conta: ' . $e->getMessage());
            throw new \Exception("Erro ao obter dados da conta: " . $e->getMessage());
        }
    }

    /**
     * Obtém os dados de uma conta pelo número
     *
     * @param mixed $request
     * @return array
     * @throws AccountNotFoundException
     */
    public function getAccountDataByNumber($request)
    {
        return $this->getAccountData($request);
    }

    /**
     * Encontra uma conta pelo número
     *
     * @param string $accountNumber
     * @return CustomerAccount
     * @throws AccountNotFoundException
     */
    private function findAccount(string $accountNumber)
    {
        $account = $this->customerAccountRepository->findByAccountNumber($accountNumber);

        if (!$account) {
            throw new AccountNotFoundException($accountNumber);
        }

        return $account;
    }

    /**
     * Monta o objeto CustomerAccountData a partir do modelo
     *
     * @param CustomerAccount $account
     * @return CustomerAccountData
     */
    private function mountCustomerAccountData($account)
    {
        return $this->customerAccountData
            ->setNumberAccount($account->number_account)
            ->setIdUser($account->user_id)
            ->setTypeAccount($account->type_account)
            ->setBalance($account->balance)
            ->setStatus($account->status)
            ->setAgency($account->agency);
    }

    /**
     * Converte um modelo CustomerAccount em um objeto CustomerAccountData
     *
     * @param CustomerAccount $account
     * @return CustomerAccountData
     */
    public function convertToCustomerAccountData(CustomerAccount $account): CustomerAccountData
    {
        return $this->mountCustomerAccountData($account);
    }

    /**
     * Cria uma nova conta de cliente com número de conta gerado automaticamente
     *
     * @param mixed $request
     * @param string $typeAccount
     * @return CustomerAccountData
     */
    public function createCustomerAccount($request, $typeAccount): CustomerAccountData
    {
        try {
            DB::beginTransaction();

            $accountNumber = $this->accountNumberGenerator->generate();

            $this->customerAccountData->setNumberAccount($accountNumber)
                ->setIdUser($request->id)
                ->setTypeAccount($typeAccount)
                ->setBalance(0)
                ->setStatus('active')
                ->setAgency('0001');

            $accountData = $this->customerAccountData->toArray();
            $account = $this->customerAccountRepository->register($accountData);

            // Inicializar saldo com transação inicial
            $transactionData = [
                'account_id' => $account->id,
                'type' => 'account_opening',
                'amount' => 0,
                'description' => 'Abertura de conta',
                'balance_before' => 0,
                'balance_after' => 0
            ];
            $this->customerAccountRepository->createTransaction($transactionData);

            DB::commit();

            return $this->customerAccountData;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar conta de cliente: ' . $e->getMessage());
            throw new \Exception("Erro ao criar conta: " . $e->getMessage());
        }
    }

    /**
     * Registra uma nova conta de cliente
     *
     * @param array $customerAccountData
     * @return void
     */
    public function registerCustomerAccount(array $customerAccountData)
    {
        return $this->customerAccountRepository->register($customerAccountData);
    }

    public function findByUserId($userId)
    {
        return $this->customerAccountRepository->findByUserId($userId);
    }
}