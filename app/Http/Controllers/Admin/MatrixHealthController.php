<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Spk\MatrixHealthAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class MatrixHealthController extends Controller
{
    public function __construct(private MatrixHealthAnalyzer $analyzer) {}

    public function index(): Response
    {
        $samples = 500;
        $seed = 42;
        $version = $this->referenceVersion();
        $cacheKey = "techmind:matrix-health:v{$version}:s{$samples}:seed{$seed}";

        $health = Cache::remember($cacheKey, 3600, function () use ($samples, $seed) {
            return $this->analyzer->analyze($samples, $seed);
        });

        $healthy = $this->isHealthy($health);

        return Inertia::render('admin/matrix-health', [
            'health' => $health,
            'healthy' => $healthy,
            'samples' => $samples,
            'seed' => $seed,
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'criteria' => ['required', 'array'],
            'careers' => ['required', 'array'],
            'samples' => ['nullable', 'integer', 'min:100', 'max:2000'],
            'seed' => ['nullable', 'integer'],
        ]);

        $samples = (int) ($data['samples'] ?? 500);
        $seed = (int) ($data['seed'] ?? 42);

        $criteria = [];
        foreach ($data['criteria'] as $item) {
            $criteria[] = [
                'code' => (string) $item['code'],
                'multiplier' => (float) ($item['multiplier'] ?? 1.0),
                'sort_order' => (int) ($item['sort_order'] ?? 0),
                'name' => (string) ($item['name'] ?? $item['code']),
            ];
        }

        $careers = [];
        foreach ($data['careers'] as $item) {
            $careers[] = [
                'code' => (string) $item['code'],
                'name' => (string) ($item['name'] ?? $item['code']),
                'sort_order' => (int) ($item['sort_order'] ?? 0),
                'scores' => (array) ($item['scores'] ?? []),
            ];
        }

        $health = $this->analyzer->analyzeMatrix($criteria, $careers, $samples, $seed);

        return response()->json([
            'health' => $health,
            'healthy' => $this->isHealthy($health),
        ]);
    }

    /** @param array<string, mixed> $health */
    private function isHealthy(array $health): bool
    {
        foreach ($health['self_consistency'] as $row) {
            if ($row['rank'] !== 1 || ($row['margin'] * 100) < 2.0) {
                return false;
            }
        }

        foreach ($health['robustness'] as $row) {
            if ($row['percent'] < 80.0) {
                return false;
            }
        }

        foreach ($health['nearest_neighbors'] as $row) {
            foreach ($row['nearest'] as $neighbor) {
                if ($neighbor === $row['nearest'][0] && $neighbor['distance'] < 3.0) {
                    return false;
                }
            }
        }

        return true;
    }

    private function referenceVersion(): int
    {
        if (! Cache::has('techmind:ref:version')) {
            Cache::put('techmind:ref:version', 1, 86400 * 30);
        }

        return (int) Cache::get('techmind:ref:version', 1);
    }
}
