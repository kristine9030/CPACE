<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Question;
use App\Models\ReviewNote;
use App\Models\Material;
use App\Models\CommunityPost;
use App\Models\CommunityResource;
use App\Models\Communication;
use App\Models\QuizSession;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        if (mb_strlen(trim($query)) < 2) {
            return response()->json([]);
        }

        $user = $request->user();
        $results = [];

        if ($user->isStudent() || $user->hasAlumniAccess()) {
            $results = $this->searchStudent($query, $user);
        }

        if ($user->isChair()) {
            $results = $this->searchChair($query, $user);
        }

        if ($user->isFaculty()) {
            $results = $this->searchFaculty($query, $user);
        }

        if ($user->isAlumni()) {
            $results = $this->searchAlumni($query, $user);
        }

        return response()->json($results);
    }

    private function like(string $query): string
    {
        return '%' . $query . '%';
    }

    private function addResult(array &$results, string $category, string $icon, string $color, string $title, string $desc, string $url): void
    {
        $results[] = compact('category', 'icon', 'color', 'title', 'desc', 'url');
    }

    private function searchStudent(string $query, User $user): array
    {
        $like = $this->like($query);
        $results = [];

        // ── Subjects ──
        $subjects = Subject::where('is_active', true)
            ->where(function ($q) use ($like) {
                $q->where('code', 'LIKE', $like)
                  ->orWhere('name', 'LIKE', $like)
                  ->orWhere('description', 'LIKE', $like);
            })->get();
        foreach ($subjects as $s) {
            $this->addResult($results, 'Subjects', 'fa-book-open', '#7B1D1D',
                $s->code . ' - ' . $s->name,
                mb_substr((string)$s->description, 0, 100),
                route('subjects.show', $s->id));
        }

        // ── Topics ──
        $topics = Topic::where(function ($q) use ($like) {
                $q->where('name', 'LIKE', $like)
                  ->orWhere('description', 'LIKE', $like);
            })->with('subject')
            ->whereHas('subject', fn($q) => $q->where('is_active', true))
            ->limit(8)->get();
        foreach ($topics as $t) {
            $this->addResult($results, 'Topics', 'fa-layer-group', '#3b82f6',
                $t->name,
                ($t->subject->code ?? '') . ' — ' . mb_substr((string)$t->description, 0, 80),
                route('subjects.show', $t->subject_id));
        }

        // ── Review Notes ──
        $notes = ReviewNote::where('student_id', $user->id)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('content', 'LIKE', $like)
                  ->orWhere('tags', 'LIKE', $like);
            })->with('subject')->limit(8)->get();
        foreach ($notes as $n) {
            $this->addResult($results, 'Review Notes', 'fa-sticky-note', '#f59e0b',
                $n->title,
                ($n->subject->name ?? 'General') . ' — ' . strip_tags(mb_substr((string)$n->content, 0, 80)),
                route('review-notes'));
        }

        // ── Materials ──
        $materials = Material::where('is_active', true)
            ->where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('description', 'LIKE', $like)
                  ->orWhere('original_name', 'LIKE', $like);
            })->with('topic.subject')->limit(5)->get();
        foreach ($materials as $m) {
            $subjName = $m->topic?->subject?->name ?? 'General';
            $mUrl = $m->topic ? route('subjects.topic', [$m->topic->subject_id, $m->topic_id]) : '#';
            $this->addResult($results, 'Materials', 'fa-file', '#8b5cf6',
                $m->title,
                $subjName . ' — ' . mb_substr((string)($m->description ?? $m->original_name ?? ''), 0, 80),
                $mUrl);
        }

        // ── Quiz Sessions (Performance) ──
        $sessions = QuizSession::where('student_id', $user->id)
            ->where(function ($q) use ($like) {
                $q->where('session_type', 'LIKE', $like)
                  ->orWhere('mode', 'LIKE', $like);
            })->with('subject')->limit(8)->get();
        foreach ($sessions as $s) {
            $subjName = $s->subject->name ?? 'All Subjects';
            $typeLabel = ucfirst(str_replace('_', ' ', $s->session_type));
            $score = $s->score_percent !== null ? round($s->score_percent) . '%' : 'In Progress';
            $this->addResult($results, 'Quiz Performance', 'fa-chart-bar', '#10b981',
                $typeLabel . ' — ' . $subjName . ' (' . $score . ')',
                ($s->completed_at ? 'Completed ' . $s->completed_at->format('M d, Y') : 'Started ' . $s->started_at->format('M d, Y')),
                route('performance'));
        }

        // ── Quiz History page entry ──
        $hasSessions = QuizSession::where('student_id', $user->id)
            ->where(function ($q) use ($like) {
                $q->where('session_type', 'LIKE', $like)
                  ->orWhere('mode', 'LIKE', $like);
            })->exists();
        if ($hasSessions) {
            $this->addResult($results, 'Quiz History', 'fa-clock-rotate', '#f59e0b',
                'Quiz History',
                'Past quiz sessions, scores, and performance trends',
                route('quiz.history'));
        }

        // ── Badges (via DB) ──
        $badgeIds = DB::table('student_badges')->where('student_id', $user->id)->pluck('badge_id');
        if ($badgeIds->isNotEmpty()) {
            $badges = DB::table('badges')->whereIn('id', $badgeIds)
                ->where(function ($q) use ($like) {
                    $q->where('name', 'LIKE', $like)
                      ->orWhere('description', 'LIKE', $like);
                })->get();
            foreach ($badges as $b) {
                $this->addResult($results, 'Achievements', 'fa-trophy', '#f59e0b',
                    $b->name,
                    mb_substr((string)$b->description, 0, 120),
                    route('achievements'));
            }
        }

        // ── Notifications (via DB) ──
        $notifs = DB::table('notifications')->where('recipient_id', $user->id)
            ->where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('message', 'LIKE', $like);
            })->limit(5)->get();
        foreach ($notifs as $n) {
            $this->addResult($results, 'Notifications', 'fa-bell', '#ef4444',
                $n->title,
                mb_substr((string)$n->message, 0, 100),
                route('notifications.index'));
        }

        // ── Messages ──
        $convIds = $user->conversations()->pluck('conversations.id');
        if ($convIds->isNotEmpty()) {
            $messages = Message::whereIn('conversation_id', $convIds)
                ->where('body', 'LIKE', $like)
                ->with('sender')
                ->limit(5)
                ->get();
            foreach ($messages as $m) {
                $this->addResult($results, 'Messages', 'fa-comment-dots', '#3b82f6',
                    ($m->sender->name ?? 'Unknown') . ': ' . mb_substr((string)$m->body, 0, 60),
                    mb_substr((string)$m->body, 0, 100),
                    route('messages.index'));
            }
        }

        // ── Study Plan (via DB) ──
        $planDates = DB::table('study_plans')->where('student_id', $user->id)
            ->where('is_active', true)->value('exam_target_date');
        if ($planDates) {
            $planItems = DB::table('study_plan_items')
                ->join('study_plans', 'study_plan_items.plan_id', '=', 'study_plans.id')
                ->join('topics', 'study_plan_items.topic_id', '=', 'topics.id')
                ->where('study_plans.student_id', $user->id)
                ->where('topics.name', 'LIKE', $like)
                ->select('study_plan_items.*', 'topics.name as topic_name')
                ->limit(5)->get();
            foreach ($planItems as $pi) {
                $this->addResult($results, 'Study Calendar', 'fa-calendar-alt', '#8b5cf6',
                    $pi->topic_name . ' (' . $pi->scheduled_date . ')',
                    'Priority: ' . ($pi->priority ?? 'medium') . ($pi->is_completed ? ' ✓ Completed' : ''),
                    route('calendar'));
            }
        }

        // ── Community Posts ──
        $posts = CommunityPost::where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('body', 'LIKE', $like);
            })->limit(5)->get();
        foreach ($posts as $p) {
            $this->addResult($results, 'Community', 'fa-people-group', '#10b981',
                $p->title ?? 'Post',
                strip_tags(mb_substr((string)$p->body, 0, 100)),
                route('community.index'));
        }

        // ── Community Resources ──
        $resources = CommunityResource::where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('description', 'LIKE', $like)
                  ->orWhere('original_name', 'LIKE', $like);
            })->limit(5)->get();
        foreach ($resources as $r) {
            $this->addResult($results, 'Resources', 'fa-book', '#059669',
                $r->title,
                mb_substr((string)($r->description ?? $r->original_name ?? ''), 0, 100),
                route('community.resources.index'));
        }

        // ── Page Descriptions (catch-all for common interface words) ──
        $this->addStudentPages($results, $query);

        return $results;
    }

    private function addStudentPages(array &$results, string $query): void
    {
        $q = mb_strtolower($query);
        $pages = [
            [
                'keywords' => 'dashboard home overview stats streak countdown welcome progress subjects',
                'title' => 'Dashboard',
                'desc' => 'Your home overview — streak, countdown to exam, recent activity, and progress summary',
                'icon' => 'fa-gauge-high', 'color' => '#7B1D1D', 'url' => route('dashboard'),
            ],
            [
                'keywords' => 'subjects topics far afar ms tax aud rfbt cpale coverage',
                'title' => 'Subjects',
                'desc' => 'Browse CPALE subjects (FAR, AFAR, MS, TAX, AUD, RFBT), topics, and learning materials',
                'icon' => 'fa-book-open', 'color' => '#7B1D1D', 'url' => route('subjects'),
            ],
            [
                'keywords' => 'quiz adaptive training testing practice questions test',
                'title' => 'Adaptive Quizzes',
                'desc' => 'Training and testing mode quizzes with adaptive difficulty based on your performance',
                'icon' => 'fa-pen-fancy', 'color' => '#7B1D1D', 'url' => route('adaptive-quizzes'),
            ],
            [
                'keywords' => 'quiz history past sessions scores results completed performance',
                'title' => 'Quiz History',
                'desc' => 'View all past quiz sessions, scores, correct answers, and time spent',
                'icon' => 'fa-clock-rotate', 'color' => '#f59e0b', 'url' => route('quiz.history'),
            ],
            [
                'keywords' => 'mock exam full length simulation timed cpale subjects',
                'title' => 'Mock Exams',
                'desc' => 'Full-length CPALE simulation with all 6 subjects, timed, access code required',
                'icon' => 'fa-file-alt', 'color' => '#7B1D1D', 'url' => route('mock-exams'),
            ],
            [
                'keywords' => 'performance analytics scores status progress mastery readiness trends chart',
                'title' => 'Performance',
                'desc' => 'Subject mastery, readiness score, performance trends, quiz history analytics',
                'icon' => 'fa-chart-bar', 'color' => '#10b981', 'url' => route('performance'),
            ],
            [
                'keywords' => 'review notes study personal notes favorite archive tags',
                'title' => 'Review Notes',
                'desc' => 'Create and manage personal study notes with tags, favorites, and archive',
                'icon' => 'fa-sticky-note', 'color' => '#f59e0b', 'url' => route('review-notes'),
            ],
            [
                'keywords' => 'calendar study plan schedule review topics dates priority',
                'title' => 'Study Calendar',
                'desc' => 'Visual study schedule with SM-2 spaced repetition planning and priority tracking',
                'icon' => 'fa-calendar-alt', 'color' => '#8b5cf6', 'url' => route('calendar'),
            ],
            [
                'keywords' => 'achievements badges points gamification trophies rewards',
                'title' => 'Achievements',
                'desc' => 'Earned badges, points, leaderboard, and gamification progress tracking',
                'icon' => 'fa-trophy', 'color' => '#f59e0b', 'url' => route('achievements'),
            ],
            [
                'keywords' => 'alumni community posts discussion feed resources library',
                'title' => 'Alumni Community',
                'desc' => 'Community feed with posts, discussions, tips, and shared resources',
                'icon' => 'fa-people-group', 'color' => '#10b981', 'url' => route('community.index'),
            ],
            [
                'keywords' => 'resource library files pdf documents download study materials',
                'title' => 'Resource Library',
                'desc' => 'Downloadable study materials, PDFs, and shared resources from the community',
                'icon' => 'fa-book', 'color' => '#059669', 'url' => route('community.resources.index'),
            ],
            [
                'keywords' => 'messages chat messenger direct message group conversation',
                'title' => 'Messages',
                'desc' => 'Group chats and direct messages with classmates and alumni',
                'icon' => 'fa-comment-dots', 'color' => '#3b82f6', 'url' => route('messages.index'),
            ],
            [
                'keywords' => 'notifications alerts updates reminders',
                'title' => 'Notifications',
                'desc' => 'In-app notifications, alerts, and reminders',
                'icon' => 'fa-bell', 'color' => '#ef4444', 'url' => route('notifications.index'),
            ],
            [
                'keywords' => 'settings account profile password configuration',
                'title' => 'Settings',
                'desc' => 'Account settings, profile configuration, and password management',
                'icon' => 'fa-cog', 'color' => '#6b7280', 'url' => route('settings'),
            ],
        ];

        foreach ($pages as $page) {
            if (str_contains($page['keywords'], $q)) {
                $this->addResult($results, 'Pages', $page['icon'], $page['color'],
                    $page['title'], $page['desc'], $page['url']);
            }
        }
    }

    private function searchChair(string $query, User $user): array
    {
        $like = $this->like($query);
        $results = [];

        // ── Students ──
        $students = User::where('role_id', Role::STUDENT)
            ->where(function ($q) use ($like) {
                $q->where('first_name', 'LIKE', $like)
                  ->orWhere('last_name', 'LIKE', $like)
                  ->orWhere('email', 'LIKE', $like)
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
            })->with('studentProfile')->limit(10)->get();
        foreach ($students as $s) {
            $sn = $s->studentProfile?->student_number ?? '';
            $label = $s->name . ($sn ? " ({$sn})" : '');
            $this->addResult($results, 'Students', 'fa-user-graduate', '#3b82f6',
                $label, $s->email, route('chair.students.show', $s->id));
        }

        // ── Faculty ──
        $faculty = User::where('role_id', Role::FACULTY)
            ->where(function ($q) use ($like) {
                $q->where('first_name', 'LIKE', $like)
                  ->orWhere('last_name', 'LIKE', $like)
                  ->orWhere('email', 'LIKE', $like)
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
            })->limit(10)->get();
        foreach ($faculty as $f) {
            $this->addResult($results, 'Faculty', 'fa-chalkboard-user', '#8b5cf6',
                $f->name, $f->email, route('chair.faculty.edit', $f->id));
        }

        // ── Subjects ──
        $subjects = Subject::where('is_active', true)
            ->where(function ($q) use ($like) {
                $q->where('code', 'LIKE', $like)
                  ->orWhere('name', 'LIKE', $like)
                  ->orWhere('description', 'LIKE', $like);
            })->get();
        foreach ($subjects as $s) {
            $this->addResult($results, 'Subjects', 'fa-layer-group', '#7B1D1D',
                $s->code . ' — ' . $s->name,
                mb_substr((string)$s->description, 0, 100),
                route('chair.subjects'));
        }

        // ── Communications ──
        $comms = Communication::where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('message', 'LIKE', $like);
            })->limit(5)->get();
        foreach ($comms as $c) {
            $this->addResult($results, 'Communications', 'fa-bullhorn', '#f59e0b',
                $c->title, mb_substr((string)$c->message, 0, 100),
                route('chair.communications'));
        }

        // ── Notifications ──
        $notifs = DB::table('notifications')->where('recipient_id', $user->id)
            ->where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('message', 'LIKE', $like);
            })->limit(5)->get();
        foreach ($notifs as $n) {
            $this->addResult($results, 'Notifications', 'fa-bell', '#ef4444',
                $n->title, mb_substr((string)$n->message, 0, 100),
                route('notifications.index'));
        }

        // ── Student Quiz Activity ──
        $activeStudents = User::where('role_id', Role::STUDENT)
            ->whereHas('quizSessions', function ($q) use ($like) {
                $q->where('session_type', 'LIKE', $like)
                  ->orWhere('mode', 'LIKE', $like);
            })->withCount('quizSessions')->limit(5)->get();
        foreach ($activeStudents as $s) {
            $this->addResult($results, 'Student Activity', 'fa-chart-line', '#10b981',
                $s->name . ' (' . $s->quiz_sessions_count . ' sessions)',
                'Has quiz activity matching your search',
                route('chair.analytics.performance'));
        }

        // ── Chair Pages ──
        $this->addChairPages($results, $query);

        return $results;
    }

    private function addChairPages(array &$results, string $query): void
    {
        $q = mb_strtolower($query);
        $pages = [
            [
                'keywords' => 'chair dashboard home stats overview analytics',
                'title' => 'Chair Dashboard',
                'desc' => 'Program Chair overview — class stats, at-risk students, faculty activity',
                'icon' => 'fa-gauge-high', 'color' => '#7B1D1D', 'url' => route('chair.dashboard'),
            ],
            [
                'keywords' => 'students management enrollment list grade year section',
                'title' => 'Student Management',
                'desc' => 'Manage student accounts, enrollment, import/export class lists',
                'icon' => 'fa-user-graduate', 'color' => '#3b82f6', 'url' => route('chair.students'),
            ],
            [
                'keywords' => 'faculty accounts management teachers instructors assign subjects',
                'title' => 'Faculty Accounts',
                'desc' => 'Manage faculty accounts, assign subjects, monitor activity',
                'icon' => 'fa-chalkboard-user', 'color' => '#8b5cf6', 'url' => route('chair.faculty'),
            ],
            [
                'keywords' => 'faculty performance evaluation activity reports',
                'title' => 'Faculty Performance',
                'desc' => 'Evaluate faculty performance, activity reports, and subject coverage',
                'icon' => 'fa-chart-column', 'color' => '#f59e0b', 'url' => route('chair.faculty.performance'),
            ],
            [
                'keywords' => 'subjects assignments topics faculty coverage',
                'title' => 'Subject Assignments',
                'desc' => 'Assign faculty to subjects, manage topics and coverage',
                'icon' => 'fa-layer-group', 'color' => '#7B1D1D', 'url' => route('chair.subjects'),
            ],
            [
                'keywords' => 'communications announcements messages broadcast',
                'title' => 'Communications',
                'desc' => 'Send announcements and messages to students, faculty, or alumni',
                'icon' => 'fa-bullhorn', 'color' => '#f59e0b', 'url' => route('chair.communications'),
            ],
            [
                'keywords' => 'class level performance analytics readiness scores',
                'title' => 'Class-Level Performance',
                'desc' => 'Class-wide performance analytics, readiness scores, subject mastery',
                'icon' => 'fa-chart-line', 'color' => '#10b981', 'url' => route('chair.analytics.performance'),
            ],
            [
                'keywords' => 'test bank coverage analytics question gaps',
                'title' => 'Test Bank Coverage',
                'desc' => 'Test bank coverage analysis, question distribution across subjects and topics',
                'icon' => 'fa-table-cells-large', 'color' => '#7B1D1D', 'url' => route('chair.analytics.test-bank-coverage'),
            ],
        ];

        foreach ($pages as $page) {
            if (str_contains($page['keywords'], $q)) {
                $this->addResult($results, 'Pages', $page['icon'], $page['color'],
                    $page['title'], $page['desc'], $page['url']);
            }
        }
    }

    private function searchFaculty(string $query, User $user): array
    {
        $like = $this->like($query);
        $results = [];
        $assignedSubjectIds = $user->assignedSubjects()->pluck('subjects.id');

        if ($assignedSubjectIds->isEmpty()) {
            $this->addFacultyPages($results, $query);
            return $results;
        }

        // ── Questions ──
        $questions = Question::where('is_active', true)
            ->where('question_text', 'LIKE', $like)
            ->whereHas('topic', fn($q) => $q->whereIn('subject_id', $assignedSubjectIds))
            ->with('topic.subject')->limit(10)->get();
        foreach ($questions as $q) {
            $this->addResult($results, 'Test Bank', 'fa-database', '#7C3AED',
                strip_tags(mb_substr((string)$q->question_text, 0, 120)),
                ($q->topic?->subject?->code ?? '') . ' / ' . ($q->topic?->name ?? ''),
                route('faculty.question.edit', $q->id));
        }

        // ── Question Choices (answers) ──
        $choices = DB::table('question_choices')
            ->join('questions', 'question_choices.question_id', '=', 'questions.id')
            ->join('topics', 'questions.topic_id', '=', 'topics.id')
            ->whereIn('topics.subject_id', $assignedSubjectIds)
            ->where('question_choices.choice_text', 'LIKE', $like)
            ->select('question_choices.*', 'questions.question_text')
            ->limit(5)->get();
        foreach ($choices as $c) {
            $this->addResult($results, 'Test Bank Choices', 'fa-list-check', '#7C3AED',
                strip_tags(mb_substr((string)$c->choice_text, 0, 100)),
                'Choice for: ' . strip_tags(mb_substr((string)$c->question_text, 0, 60)),
                route('faculty.question.edit', $c->question_id));
        }

        // ── Students in their classes ──
        $studentIds = QuizSession::whereIn('subject_id', $assignedSubjectIds)
            ->pluck('student_id')->unique();
        $students = User::whereIn('id', $studentIds)
            ->where(function ($q) use ($like) {
                $q->where('first_name', 'LIKE', $like)
                  ->orWhere('last_name', 'LIKE', $like)
                  ->orWhere('email', 'LIKE', $like)
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
            })->limit(10)->get();
        foreach ($students as $s) {
            $this->addResult($results, 'Students', 'fa-users', '#3b82f6',
                $s->name, $s->email, route('faculty.performance'));
        }

        // ── Notifications ──
        $notifs = DB::table('notifications')->where('recipient_id', $user->id)
            ->where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('message', 'LIKE', $like);
            })->limit(5)->get();
        foreach ($notifs as $n) {
            $this->addResult($results, 'Notifications', 'fa-bell', '#ef4444',
                $n->title, mb_substr((string)$n->message, 0, 100),
                route('notifications.index'));
        }

        // ── Faculty Pages ──
        $this->addFacultyPages($results, $query);

        return $results;
    }

    private function addFacultyPages(array &$results, string $query): void
    {
        $q = mb_strtolower($query);
        $pages = [
            [
                'keywords' => 'faculty dashboard home stats overview questions activity',
                'title' => 'Faculty Dashboard',
                'desc' => 'Your teaching overview — recent questions, student activity, subject stats',
                'icon' => 'fa-home', 'color' => '#7B1D1D', 'url' => route('faculty.dashboard'),
            ],
            [
                'keywords' => 'test bank questions database manage create edit delete',
                'title' => 'Test Bank',
                'desc' => 'Manage your question bank — create, edit, search questions and variants',
                'icon' => 'fa-database', 'color' => '#7C3AED', 'url' => route('faculty.test-bank'),
            ],
            [
                'keywords' => 'add question create new test item multiple choice',
                'title' => 'Add Question',
                'desc' => 'Create new test questions with multiple choice or true/false answers',
                'icon' => 'fa-plus-circle', 'color' => '#10b981', 'url' => route('faculty.question.create'),
            ],
            [
                'keywords' => 'learning materials files upload pdf resources',
                'title' => 'Learning Materials',
                'desc' => 'Upload and manage learning materials, PDFs, and resources for students',
                'icon' => 'fa-folder-open', 'color' => '#8b5cf6', 'url' => route('faculty.materials'),
            ],
            [
                'keywords' => 'student performance scores analytics progress results',
                'title' => 'Student Performance',
                'desc' => 'View student quiz performance, scores, and progress in your subjects',
                'icon' => 'fa-users', 'color' => '#3b82f6', 'url' => route('faculty.performance'),
            ],
            [
                'keywords' => 'reports analytics data export summaries',
                'title' => 'Reports',
                'desc' => 'Generate and export performance reports and analytics summaries',
                'icon' => 'fa-chart-line', 'color' => '#f59e0b', 'url' => route('faculty.reports'),
            ],
        ];

        foreach ($pages as $page) {
            if (str_contains($page['keywords'], $q)) {
                $this->addResult($results, 'Pages', $page['icon'], $page['color'],
                    $page['title'], $page['desc'], $page['url']);
            }
        }
    }

    private function searchAlumni(string $query): array
    {
        $like = $this->like($query);
        $results = [];

        // ── Community Posts ──
        $posts = CommunityPost::where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('body', 'LIKE', $like);
            })->limit(10)->get();
        foreach ($posts as $p) {
            $this->addResult($results, 'Community', 'fa-people-group', '#10b981',
                $p->title ?? 'Post',
                strip_tags(mb_substr((string)$p->body, 0, 100)),
                route('community.index'));
        }

        // ── Community Resources ──
        $resources = CommunityResource::where(function ($q) use ($like) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('description', 'LIKE', $like)
                  ->orWhere('original_name', 'LIKE', $like);
            })->limit(10)->get();
        foreach ($resources as $r) {
            $this->addResult($results, 'Resources', 'fa-book', '#059669',
                $r->title,
                mb_substr((string)($r->description ?? $r->original_name ?? ''), 0, 100),
                route('community.resources.index'));
        }

        // ── Alumni Pages ──
        $this->addAlumniPages($results, $query);

        return $results;
    }

    private function addAlumniPages(array &$results, string $query): void
    {
        $q = mb_strtolower($query);
        $pages = [
            [
                'keywords' => 'community feed posts discussion alumni network',
                'title' => 'Community Feed',
                'desc' => 'Alumni community feed with posts, discussions, and shared content',
                'icon' => 'fa-people-group', 'color' => '#10b981', 'url' => route('community.index'),
            ],
            [
                'keywords' => 'resource library files pdf documents download materials',
                'title' => 'Resource Library',
                'desc' => 'Shared study resources, PDFs, and downloadable materials',
                'icon' => 'fa-book', 'color' => '#059669', 'url' => route('community.resources.index'),
            ],
            [
                'keywords' => 'messages chat messenger direct message conversation',
                'title' => 'Messages',
                'desc' => 'Chat with other alumni and students in group and direct messages',
                'icon' => 'fa-comment-dots', 'color' => '#3b82f6', 'url' => route('messages.index'),
            ],
            [
                'keywords' => 'profile alumni account settings batch year job',
                'title' => 'My Profile',
                'desc' => 'Edit your alumni profile, batch year, job, and personal information',
                'icon' => 'fa-id-card', 'color' => '#7B1D1D', 'url' => route('alumni.profile'),
            ],
            [
                'keywords' => 'notifications alerts updates reminders',
                'title' => 'Notifications',
                'desc' => 'View your notifications and updates',
                'icon' => 'fa-bell', 'color' => '#ef4444', 'url' => route('notifications.index'),
            ],
        ];

        foreach ($pages as $page) {
            if (str_contains($page['keywords'], $q)) {
                $this->addResult($results, 'Pages', $page['icon'], $page['color'],
                    $page['title'], $page['desc'], $page['url']);
            }
        }
    }
}
