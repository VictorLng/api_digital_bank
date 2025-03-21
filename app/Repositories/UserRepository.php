<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;

class UserRepository
{
    protected User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function Register(array $userData): User
    {
        return $this->userModel->firstOrCreate([
            'cpf' => $userData['cpf'],
        ],[
            'name'  => $userData['name'],
            'cpf' => $userData['cpf'],
            'birth_date' => $userData['birth_date'],
            'phone' => $userData['phone'],
            'email' => $userData['email'],
            'password' => $userData['password'],
        ]);
    }

    public function Login($userData): User
    {
        return $this->userModel->where('email', $userData->email)->first();
    }

    public function Logout($userData): User
    {
        return $this->userModel->where('email', $userData->email)->first();
    }

    public function findByEmail($userData): User|null
    {
        return $this->userModel->where('email', $userData->email)->first();
    }
}