<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Concerns\ManagesTopics;
use App\Http\Controllers\Concerns\SubjectTheme;
use App\Http\Controllers\Controller;
use App\Models\CurriculumVersion;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Topic;
use App\Support\CurriculumScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a faculty member manage topics (add/edit/delete, and the
 * year/section/semester they're taught in) for the subjects they're
 * assigned to - the same thing the Chair does in Chair\SubjectManagementController,
 * scoped down via ManagesTopics::canManageTopicSubject().
 */
class TopicController extends Controller
{
    use ManagesTopics, SubjectTheme;

    protected function canManageTopicSubject(Subject $subject): bool
    {
        $user = Auth::user();

        return $user->isChair() || $user->assignedSubjects()->where('subjects.id', $subject->id)->exists();
    }

    /**
     * Subject picker (the faculty's assigned subjects) and, once one is
     * chosen via ?subject=, its topic tree - against whichever curriculum
     * version the Test Bank is currently showing, so draft/published
     * behaves exactly like the rest of the faculty's Test Bank.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $versionId = CurriculumScope::testBankVersionId();
        $version = $versionId ? CurriculumVersion::find($versionId) : null;

        $subjectsQuery = $user->isChair()
            ? Subject::query()
            : $user->assignedSubjects();

        $subjects = $subjectsQuery
            ->where('subjects.is_active', true)
            ->with(['topics' => fn ($query) => $query->inCurriculum($versionId)->withCount('questions')->orderBy('sort_order')->orderBy('name')])
            ->orderBy('subjects.id')
            ->get();

        $openSubject = $request->filled('subject') ? $subjects->firstWhere('id', (int) $request->query('subject')) : null;
        abort_if($request->filled('subject') && ! $openSubject, 403, "You're not assigned to this subject, so you can't manage its topics.");
        $openSubject?->setRelation('topicTree', Topic::buildTree($openSubject->topics));

        return view('faculty.topics', [
            'subjects' => $subjects,
            'openSubject' => $openSubject,
            'version' => $version,
            'readOnly' => $version !== null && ! $version->isEditable(),
            'looks' => $subjects->mapWithKeys(fn (Subject $subject) => [$subject->id => [
                'theme' => self::theme($subject->code),
                'icon' => self::subjectIcon($subject->code),
            ]]),
            'sections' => Schema::hasTable('sections')
                ? Section::where('is_active', true)->orderBy('name')->get(['name', 'year_level'])
                : collect(),
        ]);
    }
}
