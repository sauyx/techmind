<?php

namespace App\Providers;

use App\Contracts\CareerProfileRepository as CareerProfileRepositoryContract;
use App\Contracts\CareerRepository as CareerRepositoryContract;
use App\Contracts\CriterionRepository as CriterionRepositoryContract;
use App\Repositories\CareerProfileRepository;
use App\Repositories\CareerRepository;
use App\Repositories\CriterionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CriterionRepositoryContract::class, CriterionRepository::class);
        $this->app->bind(CareerRepositoryContract::class, CareerRepository::class);
        $this->app->bind(CareerProfileRepositoryContract::class, CareerProfileRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
