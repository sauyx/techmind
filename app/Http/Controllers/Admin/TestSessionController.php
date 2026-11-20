<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TestSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TestSession::query()
            ->with(['results', 'aiExplanation', 'feedback'])
            ->orderByDesc('created_at');

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }
        if ($request->filled('career')) {
            $query->whereHas('results', function ($q) use ($request) {
                $q->where('rank', 1)->where('career_name', $request->input('career'));
            });
        }
        if ($request->filled('ai_status')) {
            $query->whereHas('aiExplanation', function ($q) use ($request) {
                $q->where('status', $request->input('ai_status'));
            });
        }
        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('token', 'like', $search.'%')
                    ->orWhere('visitor_name', 'like', '%'.$search.'%');
            });
        }

        $sessions = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/sessions/index', [
            'sessions' => $sessions,
            'filters' => $request->only(['from', 'to', 'career', 'ai_status', 'search']),
        ]);
    }

    public function show(TestSession $session): Response
    {
        $session->load(['answers', 'results', 'aiExplanation', 'feedback']);

        return Inertia::render('admin/sessions/show', [
            'session' => $session,
        ]);
    }

    public function destroy(TestSession $session): RedirectResponse
    {
        $session->delete();

        return redirect()->route('admin.sessions.index')->with('success', 'Sesi berhasil dihapus.');
    }
}
