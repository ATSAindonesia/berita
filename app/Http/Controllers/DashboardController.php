<?php

namespace App\Http\Controllers;

use App\Models\NewsSummary;

class DashboardController extends Controller
{
    /**
     * Display the news summary dashboard.
     */
    public function index()
    {
        $summaries = NewsSummary::withCount('references')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $totalCount = $summaries->count();

        return view('dashboard', compact('summaries', 'totalCount'));
    }
}
