<?php

namespace App\Providers;

use App\Interfaces\AccountNumberGeneratorInterface;
use App\Interfaces\AccountNumberVerifierInterface;
use App\Services\BankAccountNumberGenerator;
use App\Services\BankAccountNumberVerifier;
use Illuminate\Support\ServiceProvider;

class AccountServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AccountNumberVerifierInterface::class, BankAccountNumberVerifier::class);
        $this->app->bind(AccountNumberGeneratorInterface::class, function ($app) {
            return new BankAccountNumberGenerator(
                $app->make(AccountNumberVerifierInterface::class),
                config('bank.code', '001')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
