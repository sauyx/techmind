<?php

namespace Tests\Feature;

use App\Models\Criterion;
use App\Models\TestSession;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultPdfAndFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_pdf_download_returns_valid_pdf_headers_and_content(): void
    {
        $session = $this->createSession('Budi');

        $response = $this->get('/hasil/'.$session->token.'/pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        $shortToken = substr((string) $session->token, 0, 8);
        $response->assertHeader('content-disposition', 'attachment; filename=TechMind-Hasil-'.$shortToken.'.pdf');

        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_visitor_feedback_storing_and_validation(): void
    {
        $session = $this->createSession('Budi');

        $response = $this->postJson('/hasil/'.$session->token.'/feedback', [
            'score' => 90,
            'comment' => 'Sangat membantu dan akurat.',
            'suggestion' => 'Tambahkan opsi simulasi gaji.',
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Penilaian Anda tersimpan. Terima kasih atas masukannya.',
            'feedback' => [
                'score' => 90,
                'comment' => 'Sangat membantu dan akurat.',
                'suggestion' => 'Tambahkan opsi simulasi gaji.',
            ],
        ]);

        $this->assertDatabaseHas('visitor_feedbacks', [
            'test_session_id' => $session->id,
            'score' => 90,
            'comment' => 'Sangat membantu dan akurat.',
        ]);

        $updateResponse = $this->postJson('/hasil/'.$session->token.'/feedback', [
            'score' => 100,
            'comment' => 'Sangat sempurna.',
            'suggestion' => null,
        ]);

        $updateResponse->assertOk();
        $this->assertDatabaseCount('visitor_feedbacks', 1);
        $this->assertDatabaseHas('visitor_feedbacks', [
            'test_session_id' => $session->id,
            'score' => 100,
            'comment' => 'Sangat sempurna.',
        ]);
    }

    public function test_visitor_feedback_rejects_out_of_range_score(): void
    {
        $session = $this->createSession('Budi');

        $response = $this->postJson('/hasil/'.$session->token.'/feedback', [
            'score' => 150,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('score');

        $responseNegative = $this->postJson('/hasil/'.$session->token.'/feedback', [
            'score' => -5,
        ]);

        $responseNegative->assertStatus(422);
        $responseNegative->assertJsonValidationErrors('score');
    }

    private function createSession(string $name = 'Budi'): TestSession
    {
        $ratings = [];
        $index = 0;
        foreach (Criterion::query()->pluck('code') as $code) {
            $ratings[$code] = [5, 4, 3, 2, 4, 3, 2, 5, 3, 4, 2, 3][$index % 12];
            $index++;
        }

        $this->post('/tes', [
            'visitor_name' => $name,
            'ratings' => $ratings,
        ]);

        return TestSession::first();
    }
}
