<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TestReport;
use App\Services\TestReportParser;
use App\Support\Auditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Automation endpoint for the Super Admin "Test Reports" page — CI (GitHub
 * Actions running Playwright/Newman), a scheduled load-benchmark job, or
 * any other script POSTs its JSON result here instead of a human pasting it
 * into the web form. Auth is a named ApiToken (see SuperAdmin\
 * ApiTokenController) belonging to a Super Admin — api.auth binds the
 * token's user, and isSuperAdmin() is checked explicitly here because
 * api.auth alone accepts any valid token, including a student's mobile
 * session.
 */
class TestReportApiController extends Controller
{
    public function store(Request $request, TestReportParser $parser)
    {
        $user = Auth::user();
        if (! $user || ! $user->isSuperAdmin()) {
            return response()->json(['message' => 'This endpoint requires a Super Admin API token.'], 403);
        }

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(TestReport::TYPES))],
            'title' => 'required|string|max:150',
            'payload' => 'required|array',
        ]);

        $summary = $parser->summarize($data['type'], $data['payload']);

        $report = TestReport::create([
            'type' => $data['type'],
            'title' => $data['title'],
            'summary' => $summary,
            'raw_payload' => json_encode($data['payload']),
            'uploaded_by' => $user->id,
            'created_at' => now(),
        ]);

        Auditor::log($user, 'test_report_uploaded', "Uploaded a {$data['type']} test report via API: \"{$data['title']}\".", 'TestReport', $report->id);

        return response()->json([
            'id' => $report->id,
            'recognized' => $summary['recognized'],
            'summary' => $summary,
        ], 201);
    }
}
