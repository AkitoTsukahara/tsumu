<?php

namespace App\Providers;

use Domain\User\UserRepository as UserRepositoryContract;
use Illuminate\Support\ServiceProvider;
use Infra\Persistence\Repositories\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
