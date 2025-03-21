<?php

namespace App\Http\Controllers;

use App\Exceptions\AccountNotFoundException;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidTransactionException;
use App\Http\Requests\CustomerAccountRequest;
use App\BO\CustomerAccountBo;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class CustomerAccountController extends Controller
{
    protected $customerAccountBo;

    public function __construct(CustomerAccountBo $customerAccountBo)
    {
        $this->customerAccountBo = $customerAccountBo;
    }

    public function addFunds(CustomerAccountRequest $request)
    {
        try {
            $result = $this->customerAccountBo->addFunds($request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Fundos adicionados com sucesso'
            ], 200);
        } catch (AccountNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (InvalidTransactionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar fundos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar a operação'
            ], 500);
        }
    }

    public function makeWithdrawal(CustomerAccountRequest $request)
    {
        try {
            $result = $this->customerAccountBo->makeWithdrawal($request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Saque realizado com sucesso'
            ], 200);
        } catch (AccountNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (InsufficientFundsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (InvalidTransactionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            Log::error('Erro ao realizar saque: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar a operação'
            ], 500);
        }
    }

    public function makeTransfer(CustomerAccountRequest $request)
    {
        try {
            $result = $this->customerAccountBo->makeTransfer($request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Transferência realizada com sucesso'
            ], 200);
        } catch (AccountNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (InsufficientFundsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (InvalidTransactionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            Log::error('Erro ao realizar transferência: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar a operação'
            ], 500);
        }
    }

    public function getBalance(CustomerAccountRequest $request)
    {
        try {
            $result = $this->customerAccountBo->getBalance($request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Saldo obtido com sucesso'
            ], 200);
        } catch (AccountNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            Log::error('Erro ao obter saldo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar a operação'
            ], 500);
        }
    }

    public function getAccountData(CustomerAccountRequest $request)
    {
        try {
            $result = $this->customerAccountBo->getAccountData($request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Dados da conta obtidos com sucesso'
            ], 200);
        } catch (AccountNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados da conta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar a operação'
            ], 500);
        }
    }

    public function getAccountDataByNumber(CustomerAccountRequest $request)
    {
        try {
            $result = $this->customerAccountBo->getAccountDataByNumber($request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Dados da conta obtidos com sucesso'
            ], 200);
        } catch (AccountNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados da conta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao processar a operação'
            ], 500);
        }
    }

    /**
     * Obtém o extrato de uma conta
     *
     * @param CustomerAccountRequest $request
     * @return JsonResponse
     */
    public function getStatement(CustomerAccountRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $statement = $this->customerAccountBo->getStatement($request);

            return response()->json([
                'success' => true,
                'data' => $statement
            ]);
        } catch (AccountNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Conta não encontrada: ' . $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter extrato: ' . $e->getMessage()
            ], 500);
        }
    }
}