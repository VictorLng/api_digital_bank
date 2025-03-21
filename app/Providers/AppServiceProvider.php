<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\HashServiceInterface;
use App\Services\HashService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registra o serviço de hash
        $this->app->bind(HashServiceInterface::class, HashService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::pattern('cpf', '[0-9]+');
    }
}
