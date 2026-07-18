<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Services\ChairAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private readonly ChairAnalyticsService $analytics)
    {
    }

    public function performance(Request $request)
    {
        $filters = $request->validate([
            'subject' => 'nullable|integer|exists:subjects,id',
        ]);
        $subjectId = isset($filters['subject']) ? (int) $filters['subject'] : null;

        return view('chair.analytics.performance', [
            'report' => $this->analytics->performanceReport($subjectId),
            'subjects' => Subject::orderBy('id')->get(),
            'selectedSubject' => $subjectId,
        ]);
    }

    public function testBankCoverage(Request $request)
    {
        $filters = $request->validate([
            'subject' => 'nullable|integer|exists:subjects,id',
        ]);
        $subjectId = isset($filters['subject']) ? (int) $filters['subject'] : null;
        $coverage = $this->analytics->coverageReport($subjectId);

        return view('chair.analytics.test-bank-coverage', [
            'coverage' => $coverage,
            'subjects' => Subject::orderBy('id')->get(),
            'selectedSubject' => $subjectId,
            'stats' => [
                'topics' => $coverage->count(),
                'active' => $coverage->sum('active'),
                'thin' => $coverage->where('status', 'thin')->count(),
                'critical' => $coverage->where('status', 'critical')->count(),
            ],
            'target' => ChairAnalyticsService::COVERAGE_TARGET,
        ]);
    }
}
