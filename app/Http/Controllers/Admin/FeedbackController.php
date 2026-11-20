<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorFeedback;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function index(Request $request): Response
    {
        $query = VisitorFeedback::query()
            ->with(['session.results'])
            ->orderByDesc('created_at');

        if ($request->filled('min_score')) {
            $query->where('score', '>=', (int) $request->input('min_score'));
        }
        if ($request->filled('max_score')) {
            $query->where('score', '<=', (int) $request->input('max_score'));
        }
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        $feedbacks = $query->paginate(20)->withQueryString();

        return Inertia::render('admin/feedbacks/index', [
            'feedbacks' => $feedbacks,
            'filters' => $request->only(['min_score', 'max_score', 'from', 'to']),
        ]);
    }
}
