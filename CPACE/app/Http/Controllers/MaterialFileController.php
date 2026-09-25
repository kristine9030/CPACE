<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Streams uploaded learning materials inline. Files live on the private disk,
 * so this is the only way to reach them: a signed-in session, or a short-lived
 * signed link (used by Office Online, which cannot log in).
 */
class MaterialFileController extends Controller
{
    public function show(Request $request, Material $material)
    {
        abort_if($material->kind !== 'file' || ! $material->file_path, 404);

        if (! $request->hasValidSignature()) {
            $user = Auth::user();
            abort_unless($user, 403);

            // Drafts are only visible to the people who manage materials.
            abort_unless($material->is_active || $user->isChair() || $user->isFaculty(), 404);
        }

        $disk = $material->storageDisk();
        abort_unless($disk, 404);

        return Storage::disk($disk)->response(
            $material->file_path,
            $material->original_name ?: basename($material->file_path),
            [
                'Content-Disposition'    => 'inline; filename="' . addslashes($material->original_name ?: basename($material->file_path)) . '"',
                'Cache-Control'          => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
