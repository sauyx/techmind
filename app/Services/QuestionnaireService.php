<?php

namespace App\Services;

use App\Contracts\CareerRepository;
use App\Contracts\CriterionRepository;
use App\Contracts\TestAnswerRepository;
use App\Contracts\TestResultRepository;
use App\Contracts\TestSessionRepository;
use App\Exceptions\InvalidRatingsException;
use App\Models\TestResult;
use App\Models\TestSession;

class QuestionnaireService
{
    public function __construct(
        private CriterionRepository $criteria,
        private CareerRepository $careers,
        private TestSessionRepository $sessions,
        private TestAnswerRepository $answers,
        private TestResultRepository $results,
        private RecommendationService $recommendation,
    ) {}

    public function start(?string $visitorName): TestSession
    {
        return $this->sessions->create($visitorName);
    }

    /**
     * @param  array<string, mixed>  $ratings
     * @return array{session: TestSession, result: TestResult, recommendation: array<string, mixed>}
     */
    public function submit(string $token, array $ratings): array
    {
        $session = $this->sessions->findByToken($token);
        if (! $session) {
            throw new InvalidRatingsException('Sesi tes tidak ditemukan.');
        }

        $activeCriteria = $this->criteria->activeOrdered();
        $criterionCodes = $activeCriteria->pluck('code')->map(fn ($code) => (string) $code)->all();

        foreach ($criterionCodes as $code) {
            if (! array_key_exists($code, $ratings)) {
                throw new InvalidRatingsException("Rating untuk kriteria {$code} wajib diisi.");
            }
        }

        foreach (array_keys($ratings) as $code) {
            if (! in_array($code, $criterionCodes, true)) {
                throw new InvalidRatingsException("Kode kriteria tidak dikenal: {$code}.");
            }
        }

        $normalizedRatings = [];
        foreach ($criterionCodes as $code) {
            $rating = $ratings[$code];
            if (is_bool($rating) || ! is_numeric($rating)) {
                throw new InvalidRatingsException("Rating {$code} harus berupa bilangan.");
            }
            $numeric = (float) $rating;
            if (floor($numeric) !== $numeric || $numeric < 1 || $numeric > 5) {
                throw new InvalidRatingsException("Rating {$code} harus bilangan bulat 1-5.");
            }
            $normalizedRatings[$code] = (int) $numeric;
        }

        $answers = [];
        foreach ($activeCriteria as $criterion) {
            $code = (string) $criterion->code;
            $answers[] = [
                'criterion_id' => $criterion->id,
                'criterion_code' => $code,
                'criterion_name' => (string) $criterion->name,
                'rating' => $normalizedRatings[$code],
                'weight_snapshot' => (float) $criterion->weight,
            ];
        }

        $this->answers->replaceForSession($session, $answers);

        $recommendation = $this->recommendation->recommend($normalizedRatings);

        $ranking = $recommendation['rankings'];
        $top = $ranking[0];

        $result = $this->results->save(
            $session,
            'SAW',
            $ranking,
            (string) $top['career_code'],
            (string) ($top['career_name'] ?? $top['career_code'])
        );

        $this->sessions->markCompleted($session);

        return [
            'session' => $session->refresh(),
            'result' => $result,
            'recommendation' => $recommendation,
        ];
    }
}
