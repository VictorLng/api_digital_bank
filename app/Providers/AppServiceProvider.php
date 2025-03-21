<?php

namespace App\Providers;

use App\BO\CustomerAccountBo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\HashServiceInterface;
use App\Services\HashService;
use Laravel\Passport\Passport;
use App\Services\Auth\AuthService;
use App\Services\User\UserRegistrationService;
use App\Repositories\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(HashServiceInterface::class, HashService::class);


        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(UserRepository::class),
                $app->make(HashServiceInterface::class),
                $app->make(CustomerAccountBo::class)
            );
        });

        $this->app->bind(UserRegistrationService::class, function ($app) {
            return new UserRegistrationService(
                $app->make(UserRepository::class),
                $app->make(HashServiceInterface::class),
                $app->make('App\BO\CustomerAccountBo')
            );
        });
        Passport::useClientModel(\Laravel\Passport\Client::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::pattern('cpf', '[0-9]+');
    }
}
