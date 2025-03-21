<?php

namespace App\Resources;


class UserData
{
    private ?string $name;
    private ?string $email;
    private ?string $cpf;
    private ?string $birth_date;
    private ?string $phone;
    private ?string $password = '';
    private ?string $newPassword = '';
    private ?string $confirmPassword = '';
    private ?string $token = '';

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

        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (empty($cpf)) {
            throw new \InvalidArgumentException('CPF must contain at least one numeric character');
        }

        $this->cpf = $cpf;
        return $this;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setBirthDate($birth_date): self
    {
        $this->birth_date = $birth_date;
        return $this;
    }

    public function getBirthDate(): ?string
    {
        return $this->birth_date;
    }

    public function setPhone($phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
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

    public function setToken($token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
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
            'birth_date' => $this->getBirthDate(),
            'phone' => $this->getPhone(),
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
            'token' => $this->getToken(),
            'CustomerAccount' => $this->getCustomerAccount()->toArray(),
        ];
    }
}