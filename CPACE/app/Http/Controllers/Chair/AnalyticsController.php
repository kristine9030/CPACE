<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Concerns\ReadsChartFilters;
use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Subject;
use App\Services\ChairAnalyticsService;
use App\Services\ChairDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    use ReadsChartFilters;

    public function __construct(private readonly ChairAnalyticsService $analytics)
    {
    }

    /**
     * Class-Level Performance. The page arrives with its data for the filters
     * in the URL; changing a filter refetches performanceData() and redraws
     * in place.
     */
    public function performance(Request $request, ChairDashboardService $dashboard)
    {
        $filters = $this->chartFilters($request);

        return view('chair.analytics.performance', [
            'report' => $this->classPerformance($dashboard, $filters),
            'filters' => $filters,
            'defaults' => $this->chartFilters(new Request()),
            'subjects' => Subject::orderBy('id')->get(),
            'sectionOptions' => Section::where('is_active', true)->orderBy('year_level')->orderBy('name')->get(['name', 'year_level']),
        ]);
    }

    public function performanceData(Request $request, ChairDashboardService $dashboard)
    {
        $filters = $this->chartFilters($request);

        return response()->json([
            'filters' => $filters,
            'report' => $this->classPerformance($dashboard, $filters),
        ]);
    }

    private function classPerformance(ChairDashboardService $dashboard, array $filters): array
    {
        return $dashboard->classPerformance(
            Carbon::parse($filters['from']),
            Carbon::parse($filters['to']),
            $filters['subject'],
            $filters['section'],
        );
    }

    /**
     * The specific students behind an "eligible students" count on the
     * dashboard's cohort breakdown, for one section or a whole year level.
     */
    public function eligibleStudents(Request $request)
    {
        $filters = $request->validate([
            'section' => 'nullable|string|exists:sections,name',
            'year' => 'nullable|integer|between:1,6',
        ]);

        $students = $this->analytics->eligibleStudentRoster(
            $filters['section'] ?? null,
            $filters['year'] ?? null
        );

        return response()->json(['students' => $students->values()]);
    }
}
