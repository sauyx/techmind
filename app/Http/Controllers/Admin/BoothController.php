<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\BoothStatusService;
use Inertia\Inertia;
use Inertia\Response;

class BoothController extends Controller
{
    public function __construct(
        private BoothStatusService $status,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/booth', [
            'status' => $this->status->summary(),
        ]);
    }
}
