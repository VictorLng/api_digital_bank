<?php

namespace App\Http\Requests;

use App\Http\Requests\CustomRulesRequest;
class UserRequest extends CustomRulesRequest
{

    // public function authorize(): bool
    // {
    //     return true;
    // }
    public function validateToRegister()
    {
        return [
            'name'  => 'required|string',
            'cpf'   => 'required|string|size:11',
            'email' => 'required|email|unique:users',
            'bank_account_type' => 'required|string|in:CORRENTE,POUPANCA',
            'password' => 'required|string|min:6|confirmed',
            'account_role' => 'required|string|in:ADMIN,USER',
        ];

    }

    public function validateToLogin()
    {
        return [
            'email'    => 'required|email',
            'password' => 'required'
        ];
    }
}