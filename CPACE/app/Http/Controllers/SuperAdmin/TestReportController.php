<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TestReport;
use App\Services\TestReportParser;
use App\Support\Auditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TestReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'frontend');

        $reports = TestReport::where('type', $type)->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('superadmin.test-reports.index', [
            'reports' => $reports,
            'types' => TestReport::TYPES,
            'activeType' => $type,
        ]);
    }

    public function store(Request $request, TestReportParser $parser)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(TestReport::TYPES))],
            'title' => 'required|string|max:150',
            'payload' => 'required_without:file|nullable|string',
            'file' => 'required_without:payload|nullable|file|max:10240',
        ]);

        $raw = $request->filled('payload')
            ? $request->input('payload')
            : file_get_contents($request->file('file')->getRealPath());

        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return back()->withErrors(['payload' => 'That file/text is not valid JSON: ' . json_last_error_msg()])->withInput();
        }

        $summary = $parser->summarize($data['type'], $decoded);

        $report = TestReport::create([
            'type' => $data['type'],
            'title' => $data['title'],
            'summary' => $summary,
            'raw_payload' => $raw,
            'uploaded_by' => Auth::id(),
            'created_at' => now(),
        ]);

        Auditor::log(Auth::user(), 'test_report_uploaded', "Uploaded a {$data['type']} test report: \"{$data['title']}\".", 'TestReport', $report->id);

        return redirect()->route('superadmin.test-reports', ['type' => $data['type']])
            ->with('status', $summary['recognized']
                ? 'Report uploaded and summarized.'
                : 'Report uploaded, but the JSON shape wasn\'t recognized — showing the raw payload only.');
    }

    public function show(int $id)
    {
        $report = TestReport::findOrFail($id);

        return view('superadmin.test-reports.show', ['report' => $report, 'types' => TestReport::TYPES]);
    }

    public function destroy(int $id)
    {
        $report = TestReport::findOrFail($id);
        $type = $report->type;
        $report->delete();

        Auditor::log(Auth::user(), 'test_report_deleted', "Deleted test report \"{$report->title}\".", 'TestReport', $id);

        return redirect()->route('superadmin.test-reports', ['type' => $type])->with('status', 'Report deleted.');
    }
}
