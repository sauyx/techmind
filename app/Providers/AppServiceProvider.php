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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('tes-submit', function (Request $request) {
            return [
                Limit::perMinute((int) config('techmind.rate_limits.tes_submit_per_session', 6))
                    ->by('session:'.$request->session()->getId()),
                Limit::perMinute((int) config('techmind.rate_limits.tes_submit_per_ip', 120))
                    ->by('ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('hasil-pdf', function (Request $request) {
            return [
                Limit::perMinute((int) config('techmind.rate_limits.hasil_pdf_per_token', 5))
                    ->by('token:'.(string) $request->route('token')),
                Limit::perMinute((int) config('techmind.rate_limits.hasil_pdf_per_ip', 60))
                    ->by('ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('feedback', function (Request $request) {
            return [
                Limit::perMinute((int) config('techmind.rate_limits.feedback_per_token', 5))
                    ->by('token:'.(string) $request->route('token')),
                Limit::perMinute((int) config('techmind.rate_limits.feedback_per_ip', 60))
                    ->by('ip:'.$request->ip()),
            ];
        });
    }
}
