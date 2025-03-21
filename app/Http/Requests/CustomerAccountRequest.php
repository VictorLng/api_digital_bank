<?php

namespace App\Http\Requests;

use App\Http\Requests\CustomRulesRequest;

class CustomerAccountRequest extends CustomRulesRequest
{
    public function validateToAddFunds()
    {
        return [
            'account_number' => 'required|string|exists:customer_accounts,number_account',
            'amount' => 'required|numeric|min:1',
        ];
    }

    public function validateToMakeWithdrawal()
    {
        return [
            'account_number' => 'required|string|exists:customer_accounts,number_account',
            'amount' => 'required|numeric|min:1',
        ];
    }

    public function validateToMakeTransfer()
    {
        return [
            'source_account_number' => 'required|string|exists:customer_accounts,number_account',
            'target_account_number' => 'required|string|exists:customer_accounts,number_account',
            'amount' => 'required|numeric|min:1',
        ];
    }

    public function validateToGetBalance()
    {
        return [
            'account_number' => 'required|string|exists:customer_accounts,number_account',
        ];
    }

    public function validateToGetAccountData()
    {
        return [
            'account_number' => 'required|string|exists:customer_accounts,number_account',
        ];
    }

    public function validateToGetAccountDataByNumber()
    {
        return [
            'account_number' => 'required|string|exists:customer_accounts,number_account',
        ];
    }

    /**
     * Validação para obter o extrato da conta
     *
     * @return array
     */
    public function validateToGetStatement()
    {
        return [
            'account_number' => 'required|string|exists:customer_accounts,number_account',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ];
    }
}