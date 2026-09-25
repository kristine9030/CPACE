<?php

namespace App\Http\Controllers;

use App\Models\CommunityResource;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommunityResourceController extends Controller
{
    /** Extensions alumni are allowed to upload to the Resource Library — same set as the feed's file attach. */
    private const ALLOWED_EXTENSIONS = 'pdf,doc,docx,ppt,pptx,xls,xlsx,csv,txt,rtf,odt';

    /**
     * The Resource Library — a persistent, filterable list of study materials
     * uploaded by alumni, separate from the chronological community feed.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = CommunityResource::with(['uploader', 'subject']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->where(function ($w) use ($term) {
                $w->where('title', 'like', $term)->orWhere('original_name', 'like', $term);
            });
        }

        $sort = $request->get('sort', 'newest');
        if ($sort === 'most_downloaded') {
            $query->orderByDesc('downloads_count')->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $resources = $query->paginate(12)->withQueryString();

        $unreadNotifications = DB::table('notifications')
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('community.resources', [
            'resources' => $resources,
            'subjects' => Subject::where('is_active', true)->orderBy('code')->get(),
            'filters' => [
                'subject_id' => $request->get('subject_id'),
                'sort' => $sort,
                'q' => $request->get('q'),
            ],
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    /**
     * Upload a new material to the library. Alumni (and the chair, for moderation) only.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->hasAlumniAccess() || $user->isChair(), 403, 'Only alumni can upload to the Resource Library.');

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'subject_id'  => ['nullable', 'integer', 'exists:subjects,id'],
            'file'        => ['required', 'file', 'mimes:' . self::ALLOWED_EXTENSIONS, 'max:20480'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        CommunityResource::create([
            'uploader_id'   => $user->id,
            'subject_id'    => $data['subject_id'] ?? null,
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'file_path'     => $file->store('community-resources', 'local'),
            'original_name' => $file->getClientOriginalName(),
            'file_category' => CommunityResource::categoryFor($extension),
            'file_size'     => $file->getSize(),
        ]);

        return back()->with('status', 'Material uploaded to the Resource Library.');
    }

    /**
     * Stream a resource inline (view only — there is no download) and count the view.
     * Needs a signed-in session or a short-lived signed link (Office Online).
     */
    public function file(Request $request, CommunityResource $resource)
    {
        if (! $request->hasValidSignature()) {
            abort_unless(Auth::check(), 403);
        }

        $disk = $resource->storageDisk();
        abort_unless($disk, 404);

        $resource->increment('downloads_count');

        $name = $resource->original_name ?: basename($resource->file_path);

        return Storage::disk($disk)->response($resource->file_path, $name, [
            'Content-Disposition'    => 'inline; filename="' . addslashes($name) . '"',
            'Cache-Control'          => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Delete a resource (uploader or chair only) and its physical file.
     */
    public function destroy(CommunityResource $resource)
    {
        $user = Auth::user();
        abort_unless($user->isChair() || $resource->uploader_id === $user->id, 403);

        if ($resource->file_path) {
            Storage::disk('local')->delete($resource->file_path);
            Storage::disk('public')->delete($resource->file_path); // legacy uploads
        }

        $resource->delete();

        return back()->with('status', 'Material removed from the Resource Library.');
    }
}
