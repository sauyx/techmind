<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCareerRequest;
use App\Http\Requests\Admin\UpdateCareerRequest;
use App\Models\Career;
use App\Services\Admin\CareerManagementService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CareerController extends Controller
{
    public function __construct(
        private CareerManagementService $careers,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/careers/index', [
            'careers' => $this->careers->list(),
            'standard_total_score' => $this->careers->standardTotalScore(),
            'max_total_score' => $this->careers->maxTotalScore(),
            'criteria' => $this->careers->criteria(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/careers/form', [
            'career' => null,
            'criteria' => $this->careers->criteria(),
            'standard_total_score' => $this->careers->standardTotalScore(),
        ]);
    }

    public function store(StoreCareerRequest $request): RedirectResponse
    {
        $this->careers->create($request->validated());

        return redirect()
            ->route('admin.careers.index')
            ->with('success', 'Karier baru berhasil dibuat.');
    }

    public function edit(Career $career): Response
    {
        $detail = $this->careers->find((int) $career->id);

        abort_if($detail === null, 404);

        return Inertia::render('admin/careers/form', [
            'career' => $detail,
            'criteria' => $this->careers->criteria(),
            'standard_total_score' => $this->careers->standardTotalScore(),
        ]);
    }

    public function toggle(Career $career): RedirectResponse
    {
        $this->careers->toggleActive($career);

        return back()->with('success', 'Status karier berhasil diperbarui.');
    }

    public function update(UpdateCareerRequest $request, Career $career): RedirectResponse
    {
        $this->careers->update($career, $request->validated());

        return redirect()
            ->route('admin.careers.index')
            ->with('success', 'Karier dan matriks profil berhasil diperbarui.');
    }
}
