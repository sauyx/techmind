<?php

namespace App\Services\Admin;

use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;
use App\Contracts\TestSessionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class BoothStatusService
{
    public function __construct(
        private CriterionRepository $criteria,
        private CareerRepository $careers,
        private TestSessionRepositoryInterface $sessions,
    ) {}

    public function summary(): array
    {
        $driver = (string) config('techmind.ai.driver');

        return [
            'active_criteria' => $this->criteria->activeOrdered()->count(),
            'active_careers' => $this->careers->activeWithProfiles()->count(),
            'ai_driver' => $driver,
            'ai_mode' => $driver === 'template' ? 'Offline Template' : 'API',
            'total_sessions' => $this->sessions->countCompleted(),
            'app_url' => (string) config('app.url'),
            'demo_mode' => (bool) config('techmind.demo_mode'),
            'database_ready' => $this->databaseReady(),
        ];
    }

    private function databaseReady(): bool
    {
        try {
            DB::select('select 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
