<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /** Extensions faculty are allowed to upload as study materials. */
    private const ALLOWED_EXTENSIONS = 'pdf,doc,docx,ppt,pptx,xls,xlsx,csv,txt,rtf,odt,jpg,jpeg,png,gif,webp,mp4,zip,rar';

    /**
     * Learning Materials manager. Faculty see their assigned subjects, drill
     * into a topic and manage the materials attached to it.
     */
    public function index(Request $request)
    {
        $faculty = Auth::user();

        // Only the subjects the Program Chair assigned to this faculty member.
        // Chair/admin can manage everything.
        $subjects = $faculty->isChair()
            ? Subject::where('is_active', true)->orderBy('id')->get()
            : $faculty->assignedSubjects()->where('is_active', true)->orderBy('id')->get();

        $subjectIds = $subjects->pluck('id');

        $selectedSubject = null;
        $topics = collect();
        $selectedTopic = null;
        $materials = collect();

        if ($subjects->isNotEmpty()) {
            $selectedSubject = $request->filled('subject')
                ? $subjects->firstWhere('id', (int) $request->input('subject'))
                : $subjects->first();

            if ($selectedSubject) {
                $topics = Topic::where('subject_id', $selectedSubject->id)
                    ->where('is_active', true)
                    ->withCount('materials')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();

                if ($topics->isNotEmpty()) {
                    $selectedTopic = $request->filled('topic')
                        ? $topics->firstWhere('id', (int) $request->input('topic'))
                        : $topics->first();

                    if ($selectedTopic) {
                        $materials = Material::where('topic_id', $selectedTopic->id)
                            ->with('uploader')
                            ->orderByDesc('id')
                            ->get();
                    }
                }
            }
        }

        return view('faculty.materials', compact(
            'subjects', 'selectedSubject', 'topics', 'selectedTopic', 'materials'
        ));
    }

    /**
     * Store one or more new materials — either uploaded files (bulk-capable)
     * or a single external link.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'topic_id'     => ['required', 'exists:topics,id'],
            'title'        => ['nullable', 'string', 'max:255', 'required_if:kind,link'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'kind'         => ['required', 'in:file,link'],
            'file'         => ['required_if:kind,file', 'array', 'min:1'],
            'file.*'       => ['file', 'mimes:' . self::ALLOWED_EXTENSIONS, 'max:20480'],
            'external_url' => ['required_if:kind,link', 'nullable', 'url', 'max:2048'],
            'status'       => ['nullable', 'in:draft,publish'],
        ]);

        $this->authorizeTopic((int) $data['topic_id']);

        $isActive = ($data['status'] ?? 'publish') !== 'draft';
        $count = 0;

        if ($data['kind'] === 'file') {
            $files = $request->file('file', []);

            foreach ($files as $file) {
                $extension = strtolower($file->getClientOriginalExtension());

                // A single upload keeps the faculty-typed title; a bulk batch
                // names each material after its own file since one title
                // can't sensibly describe several different files.
                $title = (count($files) === 1 && filled($data['title']))
                    ? $data['title']
                    : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                $material = new Material([
                    'topic_id'    => $data['topic_id'],
                    'uploaded_by' => Auth::id(),
                    'title'       => $title,
                    'description' => $data['description'] ?? null,
                    'kind'        => 'file',
                    'is_active'   => $isActive,
                ]);

                $material->file_path = $file->store('materials', 'local');
                $material->original_name = $file->getClientOriginalName();
                $material->file_size = $file->getSize();
                $material->file_category = Material::categoryFor($extension);
                $material->save();

                $count++;
            }
        } else {
            $material = new Material([
                'topic_id'    => $data['topic_id'],
                'uploaded_by' => Auth::id(),
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'kind'        => 'link',
                'is_active'   => $isActive,
            ]);

            $material->external_url = $data['external_url'];
            $material->file_category = 'link';
            $material->save();

            $count = 1;
        }

        $verb = $isActive ? 'published' : 'saved as draft';
        $message = $count > 1 ? "{$count} materials {$verb}." : 'Material ' . $verb . '.';

        return back()->with('status', $message);
    }

    /**
     * Flip a material between draft and published. Drafts stay hidden from
     * students (Student\SubjectController only queries is_active materials)
     * so faculty can prep a file before it's visible in class.
     */
    public function toggleStatus(Material $material)
    {
        $this->authorizeTopic($material->topic_id);

        $material->update(['is_active' => ! $material->is_active]);

        return back()->with('status', 'Material ' . ($material->is_active ? 'published' : 'moved back to draft') . '.');
    }

    /**
     * Delete a material (and its file if it was an upload).
     */
    public function destroy(Material $material)
    {
        $this->authorizeTopic($material->topic_id);

        if ($material->file_path) {
            Storage::disk('local')->delete($material->file_path);
            Storage::disk('public')->delete($material->file_path); // legacy uploads
        }

        $material->delete();

        return back()->with('status', 'Material deleted.');
    }

    /**
     * Ensure the current faculty member is allowed to manage the given topic's
     * subject. Chair/admin bypass the assignment check.
     */
    private function authorizeTopic(int $topicId): void
    {
        $user = Auth::user();

        if ($user->isChair()) {
            return;
        }

        $subjectId = Topic::where('id', $topicId)->value('subject_id');

        $assigned = $user->assignedSubjects()->where('subjects.id', $subjectId)->exists();

        abort_unless($assigned, 403, 'You are not assigned to this subject.');
    }
}
