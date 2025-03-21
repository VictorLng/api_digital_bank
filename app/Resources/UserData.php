<?php

namespace App\Resources;


class UserData
{
    private ?string $name;
    private ?string $email;
    private ?string $cpf;
    private ?string $password;
    private ?string $newPassword;
    private ?string $confirmPassword;
    private ?string $remember_token;
    private CustomerAccountData $customerAccont;

    public function __construct(CustomerAccountData $customerAccont)
    {
        $this->customerAccont = $customerAccont;
    }
    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setCpf($cpf): self
    {
        $this->cpf = $cpf;
        return $this;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setEmail($email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPassword($password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setRememberToken($remember_token): self
    {
        $this->remember_token = $remember_token;
        return $this;
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setNewPassword($newPassword): self
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setConfirmPassword($confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;
        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function getCustomerAccount(): CustomerAccountData
    {
        return $this->customerAccont;
    }

    public function setCustomerAccount(CustomerAccountData $customerAccont): self
    {
        $this->customerAccont = $customerAccont;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'cpf' => $this->getCpf(),
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
            'CustomerAccount' => $this->getCustomerAccount()->toArray(),
            'remember_token' => $this->getRememberToken() ?? null,
        ];
    }
}