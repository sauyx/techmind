<?php

namespace App\Providers;

use App\Contracts\CareerProfileRepository as CareerProfileRepositoryContract;
use App\Contracts\CareerRepository as CareerRepositoryContract;
use App\Contracts\CriterionRepository as CriterionRepositoryContract;
use App\Contracts\TestAnswerRepository as TestAnswerRepositoryContract;
use App\Contracts\TestResultRepository as TestResultRepositoryContract;
use App\Contracts\TestSessionRepository as TestSessionRepositoryContract;
use App\Repositories\CareerProfileRepository;
use App\Repositories\CareerRepository;
use App\Repositories\CriterionRepository;
use App\Repositories\TestAnswerRepository;
use App\Repositories\TestResultRepository;
use App\Repositories\TestSessionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CriterionRepositoryContract::class, CriterionRepository::class);
        $this->app->bind(TestSessionRepositoryContract::class, TestSessionRepository::class);
        $this->app->bind(TestAnswerRepositoryContract::class, TestAnswerRepository::class);
        $this->app->bind(TestResultRepositoryContract::class, TestResultRepository::class);
        $this->app->bind(CareerRepositoryContract::class, CareerRepository::class);
        $this->app->bind(CareerProfileRepositoryContract::class, CareerProfileRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
