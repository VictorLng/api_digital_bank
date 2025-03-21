<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BO\CustomerAccountBo;

class CustomerAccountController extends Controller
{
    protected $CustomerAccountBo;

    public function __construct(CustomerAccountBo $CustomerAccountBo)
    {
        $this->CustomerAccountBo = $CustomerAccountBo;
    }

}