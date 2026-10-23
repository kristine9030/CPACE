<?php

namespace App\Http\Controllers;

use App\Support\QuestionExhibit;
use Illuminate\Support\Facades\Storage;

/**
 * Streams pictures used in questions (inline pictures and older attachments).
 *
 * Served at the same /storage/question-images/... address the public symlink
 * would use, so a server without `storage:link` (or with symlink() disabled)
 * still shows them; where the link exists the web server answers first.
 */
class QuestionImageController extends Controller
{
    public function show(string $file)
    {
        $path = QuestionExhibit::DIRECTORY . '/' . $file;
        abort_unless(QuestionExhibit::validImagePath($path) && Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
