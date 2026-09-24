<?php

namespace App\Providers;

use App\Service\Query\Equipment\EquipmentListQuery as EquipmentListQueryContract;
use Domain\Equipment\EquipmentRepository as EquipmentRepositoryContract;
use Domain\Exercise\ExerciseRepository as ExerciseRepositoryContract;
use Domain\User\UserRepository as UserRepositoryContract;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Infra\Persistence\Queries\EquipmentListQuery;
use Infra\Persistence\Repositories\EquipmentRepository;
use Infra\Persistence\Repositories\ExerciseRepository;
use Infra\Persistence\Repositories\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EquipmentListQueryContract::class, EquipmentListQuery::class);
        $this->app->bind(EquipmentRepositoryContract::class, EquipmentRepository::class);
        $this->app->bind(ExerciseRepositoryContract::class, ExerciseRepository::class);
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
        $this->app->bind(
            StatefulGuard::class,
            fn (Application $app): StatefulGuard => $app->make(AuthManager::class)->guard(),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
