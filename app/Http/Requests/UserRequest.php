<?php

namespace App\Http\Requests;

use App\Http\Requests\CustomRulesRequest;
class UserRequest extends CustomRulesRequest
{

    public function authorize(): bool
    {
        return true;
    }
    public function validateToRegister()
    {
        return [
            'name'  => 'required|string',
            'cpf'   => 'required|string|size:11',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
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