<?php
namespace App\BO\Interfaces;

interface UserInterface {

    /**
     * Summary of Register
     * @return \App\Resources\UserData
     */
    public function Register($request): \App\Resources\UserData;

    /**
     * Summary of Login
     * @return \App\Resources\UserData
     */
    public function Login($request): \App\Resources\UserData;

    /**
     * Summary of Logout
     * @return bool
     */
    public function Logout($request): bool;

    /**
     * Summary of forgotPassword
     * @return bool
     */
    public function forgotPassword($request): bool;

    /**
     * Summary of passwordChange
     * @return bool
     */
    public function passwordChange($request): bool;
}