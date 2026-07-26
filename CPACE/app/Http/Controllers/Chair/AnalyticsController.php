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
        $rollup = $this->analytics->subjectCoverageRollup($subjectId);
        $active = (int) $coverage->sum('active');

        return view('chair.analytics.test-bank-coverage', [
            'coverage' => $coverage,
            'rollup' => $rollup,
            'growth' => $this->analytics->bankGrowth(6, $subjectId),
            // The worst-covered areas are the actionable list; the full table stays below.
            'gaps' => $coverage->sortByDesc('gap')->take(12)->values(),
            'subjects' => Subject::orderBy('id')->get(),
            'selectedSubject' => $subjectId,
            'stats' => [
                'areas' => $coverage->count(),
                'subtopics' => (int) $coverage->sum('subtopics'),
                'active' => $active,
                'inactive' => (int) $coverage->sum('total') - $active,
                'thin' => $coverage->where('status', 'thin')->count(),
                'critical' => $coverage->where('status', 'critical')->count(),
                'adequate' => $coverage->where('status', 'adequate')->count(),
                'gap' => (int) $coverage->sum('gap'),
                'coverage' => $coverage->count() > 0
                    ? (int) min(100, round($active / ($coverage->count() * ChairAnalyticsService::COVERAGE_TARGET) * 100))
                    : 0,
            ],
            'target' => ChairAnalyticsService::COVERAGE_TARGET,
        ]);
    }
}
