<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\EventRepository;

use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Repositories\ReservationRepository;

// Services
use App\Services\Contracts\UserServiceInterface;
use App\Services\UserService;

use App\Services\Contracts\EventServiceInterface;
use App\Services\EventService;

use App\Services\Contracts\ReservationServiceInterface;
use App\Services\ReservationService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            UserServiceInterface::class,
            UserService::class
        );

        $this->app->bind(
            EventRepositoryInterface::class,
            EventRepository::class
        );

        $this->app->bind(
            EventServiceInterface::class,
            EventService::class
        );

        $this->app->bind(
            ReservationRepositoryInterface::class,
            ReservationRepository::class
        );

        $this->app->bind(
            ReservationServiceInterface::class,
            ReservationService::class
        );
    }

    public function boot(): void
    {
        //
    }
}
