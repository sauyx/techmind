<?php

namespace App\Providers;

use App\Contracts\CareerProfileRepository as CareerProfileRepositoryContract;
use App\Contracts\CareerRepository as CareerRepositoryContract;
use App\Contracts\CriterionRepository as CriterionRepositoryContract;
use App\Contracts\TestSessionRepositoryInterface;
use App\Repositories\CareerProfileRepository;
use App\Repositories\CareerRepository;
use App\Repositories\CriterionRepository;
use App\Repositories\EloquentTestSessionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CriterionRepositoryContract::class, CriterionRepository::class);
        $this->app->bind(TestSessionRepositoryInterface::class, EloquentTestSessionRepository::class);
        $this->app->bind(CareerRepositoryContract::class, CareerRepository::class);
        $this->app->bind(CareerProfileRepositoryContract::class, CareerProfileRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
