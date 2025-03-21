<?php

namespace App\Resources;

class CustomerAccountData
{
    private $balance;
    private $number_account;
    private $agency;
    private $type_account;
    private $status;

    public function getBalance()
    {
        return $this->balance;
    }

    public function getNumberAccount()
    {
        return $this->number_account;
    }

    public function getAgency()
    {
        return $this->agency;
    }

    public function getTypeAccount()
    {
        return $this->type_account;
    }

    public function getStatus()
    {
        return $this->status;
    }

    // Setters
    public function setBalance($balance)
    {
        $this->balance = $balance;
        return $this;
    }

    public function setNumberAccount($number_account)
    {
        $this->number_account = $number_account;
        return $this;
    }

    public function setAgency($agency)
    {
        $this->agency = $agency;
        return $this;
    }

    public function setTypeAccount($type_account)
    {
        $this->type_account = $type_account;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function toArray()
    {
        return [
            'balance' => $this->balance,
            'number_account' => $this->number_account,
            'agency' => $this->agency,
            'type_account' => $this->type_account,
            'status' => $this->status,
        ];
    }
}
