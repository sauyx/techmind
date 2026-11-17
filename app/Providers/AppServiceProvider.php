<?php

namespace App\Providers;

use App\Contracts\AiExplanationRepository;
use App\Contracts\CareerProfileRepository as CareerProfileRepositoryContract;
use App\Contracts\CareerRepository as CareerRepositoryContract;
use App\Contracts\CriterionRepository as CriterionRepositoryContract;
use App\Contracts\TestSessionRepositoryInterface;
use App\Contracts\VisitorFeedbackRepository;
use App\Repositories\CareerProfileRepository;
use App\Repositories\CareerRepository;
use App\Repositories\CriterionRepository;
use App\Repositories\EloquentAiExplanationRepository;
use App\Repositories\EloquentTestSessionRepository;
use App\Repositories\EloquentVisitorFeedbackRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CriterionRepositoryContract::class, CriterionRepository::class);
        $this->app->bind(TestSessionRepositoryInterface::class, EloquentTestSessionRepository::class);
        $this->app->bind(CareerRepositoryContract::class, CareerRepository::class);
        $this->app->bind(CareerProfileRepositoryContract::class, CareerProfileRepository::class);
        $this->app->bind(AiExplanationRepository::class, EloquentAiExplanationRepository::class);
        $this->app->bind(VisitorFeedbackRepository::class, EloquentVisitorFeedbackRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
