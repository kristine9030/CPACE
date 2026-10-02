<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Support\Auditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Named, admin-issued API tokens for automation — CI uploading a test
 * report (see Api\TestReportApiController), a scheduled benchmark, or any
 * other script that needs to call CPACE's API as this Super Admin. Distinct
 * from the mobile app's own unnamed, always-30-day login tokens (AuthApiController).
 */
class ApiTokenController extends Controller
{
    public function index()
    {
        $tokens = ApiToken::whereNotNull('name')->with('user')->orderByDesc('created_at')->get();

        return view('superadmin.api-tokens', ['tokens' => $tokens]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'expires_in_days' => 'nullable|integer|min:1|max:3650',
        ]);

        $token = ApiToken::issueNamed(Auth::id(), $data['name'], $data['expires_in_days'] ?? null);

        Auditor::log(Auth::user(), 'api_token_issued', "Issued API token \"{$data['name']}\".", 'ApiToken', $token->id);

        // The raw token is only ever shown here, once.
        return redirect()->route('superadmin.api-tokens')->with('newToken', $token->token)->with('newTokenName', $data['name']);
    }

    public function destroy(int $id)
    {
        $token = ApiToken::whereNotNull('name')->findOrFail($id);
        $name = $token->name;
        $token->delete();

        Auditor::log(Auth::user(), 'api_token_revoked', "Revoked API token \"{$name}\".", 'ApiToken', $id);

        return redirect()->route('superadmin.api-tokens')->with('status', "Token \"{$name}\" revoked.");
    }
}
