<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Live "is this email already taken / does the domain look real" check used
 * by the student and faculty enrollment forms so the Program Chair gets an
 * instant warning before submitting, instead of only finding out after a
 * full-page validation error.
 */
class EmailAvailabilityController extends Controller
{
    public function check(Request $request)
    {
        $email = trim((string) $request->query('email'));
        $excludeId = $request->query('exclude_id');

        $validFormat = (bool) filter_var($email, FILTER_VALIDATE_EMAIL);

        $taken = $validFormat && User::where('email', $email)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();

        $mxOk = null;
        if ($validFormat) {
            $domain = substr(strrchr($email, '@'), 1);
            $mxOk = @checkdnsrr($domain, 'MX') || @checkdnsrr($domain, 'A');
        }

        return response()->json([
            'valid_format' => $validFormat,
            'taken' => $taken,
            'mx_ok' => $mxOk,
        ]);
    }
}
