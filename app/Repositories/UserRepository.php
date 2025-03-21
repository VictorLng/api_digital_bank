<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public static function Register(array $userData): User
    {
        return User::create($userData);
    }

    public static function Login($userData): User
    {
        return User::where('email', $userData->email)->first();
    }

    public static function Logout($userData): User
    {
        return User::where('email', $userData->email)->first();
    }

    public static function forgotPassword($userData): User
    {
        return User::where('email', $userData->email)->first();
    }

    public static function passwordChange($userData): bool
    {
        return User::update(
                    [
                        'email'    => $userData->email,
                        'password' => $userData->password
                    ],
                    [
                        'password'=> $userData->newPassword
                    ]);
    }

    public static function findByEmail($userData): User|null
    {
        return User::where('email', $userData->email)->first();
    }

}