<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'description',
        'reference_id',
        'related_account_id',
        'balance_before',
        'balance_after'
    ];

    /**
     * Relação com a conta
     */
    public function account()
    {
        return $this->belongsTo(CustomerAccount::class, 'account_id');
    }

    /**
     * Relação com a conta relacionada (para transferências)
     */
    public function relatedAccount()
    {
        return $this->belongsTo(CustomerAccount::class, 'related_account_id');
    }
}
