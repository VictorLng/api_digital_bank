<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Código do Banco
    |--------------------------------------------------------------------------
    |
    | Este código é usado para gerar números de conta únicos.
    |
    */
    'code' => env('BANK_CODE', '001'),
    'agency' => env('BANK_AGENCY', '0001'),
];
