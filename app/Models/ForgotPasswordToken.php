<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForgotPasswordToken extends Model
{
    /**
     * Indica se o modelo deve ser registrado com timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Os atributos que são atribuíveis em massa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
        'created_at',
        'expires_at',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
