<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\FeedbackExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeedbackExportController extends Controller
{
    public function __construct(
        private FeedbackExportService $export,
    ) {}

    public function export(): StreamedResponse
    {
        return $this->export->export();
    }
}
