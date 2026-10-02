<?php

namespace App\Providers;

use App\Service\Query\Equipment\EquipmentListQuery as EquipmentListQueryContract;
use App\Service\Query\Exercise\ExerciseListQuery as ExerciseListQueryContract;
use App\Service\Query\Workout\InProgressWorkoutQuery as InProgressWorkoutQueryContract;
use App\Service\Query\Workout\WorkoutExerciseListQuery as WorkoutExerciseListQueryContract;
use Domain\Equipment\EquipmentRepository as EquipmentRepositoryContract;
use Domain\Exercise\ExerciseRepository as ExerciseRepositoryContract;
use Domain\User\UserRepository as UserRepositoryContract;
use Domain\Workout\WorkoutRepository as WorkoutRepositoryContract;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Infra\Persistence\Queries\EquipmentListQuery;
use Infra\Persistence\Queries\ExerciseListQuery;
use Infra\Persistence\Queries\InProgressWorkoutQuery;
use Infra\Persistence\Queries\WorkoutExerciseListQuery;
use Infra\Persistence\Repositories\EquipmentRepository;
use Infra\Persistence\Repositories\ExerciseRepository;
use Infra\Persistence\Repositories\UserRepository;
use Infra\Persistence\Repositories\WorkoutRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EquipmentListQueryContract::class, EquipmentListQuery::class);
        $this->app->bind(ExerciseListQueryContract::class, ExerciseListQuery::class);
        $this->app->bind(InProgressWorkoutQueryContract::class, InProgressWorkoutQuery::class);
        $this->app->bind(WorkoutExerciseListQueryContract::class, WorkoutExerciseListQuery::class);
        $this->app->bind(EquipmentRepositoryContract::class, EquipmentRepository::class);
        $this->app->bind(ExerciseRepositoryContract::class, ExerciseRepository::class);
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
        $this->app->bind(WorkoutRepositoryContract::class, WorkoutRepository::class);
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
