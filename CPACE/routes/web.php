<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\AchievementController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AccountSetupController;
use App\Http\Controllers\Student\CalendarController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Faculty\FacultyDashboardController;
use App\Http\Controllers\Faculty\FacultyPerformanceController;
use App\Http\Controllers\Faculty\FacultyReportController;
use App\Http\Controllers\Chair\ProgramChairController;
use App\Http\Controllers\Chair\FacultyOversightController;
use App\Http\Controllers\Chair\SubjectManagementController;
use App\Http\Controllers\Chair\StudentManagementController;
use App\Http\Controllers\Student\PerformanceController;
use App\Http\Controllers\Student\QuizController;
use App\Http\Controllers\Student\MockExamController;
use App\Http\Controllers\Student\ReviewNoteController;
use App\Http\Controllers\Student\AiTutorController;
use App\Http\Controllers\Faculty\TestBankController;
use App\Http\Controllers\Faculty\MaterialController;
use App\Http\Controllers\Student\SubjectController;
use App\Http\Controllers\Chair\CommunicationController;
use App\Http\Controllers\Chair\AnalyticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityResourceController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Alumni\ProfileController as AlumniProfileController;

Route::get('/', function () {
    return view('welcome');
});

// Social OAuth Routes (accessible regardless of auth state)
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/auth/microsoft', [AuthController::class, 'redirectToMicrosoft'])->name('auth.microsoft');
Route::get('/auth/microsoft/callback', [AuthController::class, 'handleMicrosoftCallback']);

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // First-login onboarding flow (change one-time password + build study plan)
    Route::get('/account-setup', [AccountSetupController::class, 'show'])->name('account-setup');
    Route::post('/account-setup', [AccountSetupController::class, 'store'])->name('account-setup.store');
    Route::get('/welcome-setup', [AccountSetupController::class, 'welcome'])->name('onboarding.welcome');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    // Program Chair Routes (Admin role)
    Route::prefix('chair')->name('chair.')->middleware('chair')->group(function () {
        Route::get('/dashboard', [ProgramChairController::class, 'dashboard'])->name('dashboard');

        // System-wide student performance and test-bank analytics
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/performance', [AnalyticsController::class, 'performance'])->name('performance');
            Route::get('/test-bank-coverage', [AnalyticsController::class, 'testBankCoverage'])->name('test-bank-coverage');
        });

        // Student enrollment, monitoring and reporting
        Route::get('/students', [StudentManagementController::class, 'index'])->name('students');
        Route::get('/students/create', [StudentManagementController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentManagementController::class, 'store'])->name('students.store');
        // Bulk-enrollment UI (upload class list → auto-provision GSuite accounts)
        Route::get('/students/import', fn () => view('chair.students-import'))->name('students.import.form');
        Route::post('/students/import', [StudentManagementController::class, 'import'])->name('students.import');
        Route::get('/students/import-template', [StudentManagementController::class, 'template'])->name('students.template');
        Route::get('/students/export/csv', [StudentManagementController::class, 'exportCsv'])->name('students.export.csv');
        Route::get('/students/export/pdf', [StudentManagementController::class, 'exportPdf'])->name('students.export.pdf');
        Route::get('/students/{id}', [StudentManagementController::class, 'show'])->name('students.show');
        Route::get('/students/{id}/edit', [StudentManagementController::class, 'edit'])->name('students.edit');
        Route::put('/students/{id}', [StudentManagementController::class, 'update'])->name('students.update');
        Route::post('/students/{id}/toggle', [StudentManagementController::class, 'toggle'])->name('students.toggle');

        // Faculty account management
        Route::get('/faculty', [ProgramChairController::class, 'faculty'])->name('faculty');
        Route::get('/faculty/create', [ProgramChairController::class, 'createFaculty'])->name('faculty.create');
        Route::post('/faculty', [ProgramChairController::class, 'storeFaculty'])->name('faculty.store');
        Route::get('/faculty/{id}/edit', [ProgramChairController::class, 'editFaculty'])->name('faculty.edit');
        Route::put('/faculty/{id}', [ProgramChairController::class, 'updateFaculty'])->name('faculty.update');
        Route::post('/faculty/{id}/assign', [ProgramChairController::class, 'assignSubjects'])->name('faculty.assign');
        Route::post('/faculty/{id}/toggle', [ProgramChairController::class, 'toggleFaculty'])->name('faculty.toggle');
        Route::get('/faculty-performance', [FacultyOversightController::class, 'performance'])->name('faculty.performance');
        Route::get('/faculty/{id}/activity', [FacultyOversightController::class, 'activity'])->name('faculty.activity');

        // Subject assignment overview
        Route::get('/subjects', [SubjectManagementController::class, 'index'])->name('subjects');
        Route::post('/subjects', [SubjectManagementController::class, 'storeSubject'])->name('subjects.store');
        Route::put('/subjects/{subject}', [SubjectManagementController::class, 'updateSubject'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [SubjectManagementController::class, 'destroySubject'])->name('subjects.destroy');
        Route::post('/subjects/{subject}/topics', [SubjectManagementController::class, 'storeTopic'])->name('subjects.topics.store');
        Route::put('/subjects/{subject}/topics/{topic}', [SubjectManagementController::class, 'updateTopic'])->name('subjects.topics.update');
        Route::delete('/subjects/{subject}/topics/{topic}', [SubjectManagementController::class, 'destroyTopic'])->name('subjects.topics.destroy');

        // Announcements and internal messages
        Route::get('/communications', [CommunicationController::class, 'index'])->name('communications');
        Route::post('/communications', [CommunicationController::class, 'store'])->name('communications.store');

        // Alumni account management (Alumni Community posters)
        Route::get('/alumni', [ProgramChairController::class, 'alumni'])->name('alumni');
        Route::get('/alumni/create', [ProgramChairController::class, 'createAlumni'])->name('alumni.create');
        Route::post('/alumni', [ProgramChairController::class, 'storeAlumni'])->name('alumni.store');
        Route::get('/alumni/{id}/edit', [ProgramChairController::class, 'editAlumni'])->name('alumni.edit');
        Route::put('/alumni/{id}', [ProgramChairController::class, 'updateAlumni'])->name('alumni.update');
        Route::post('/alumni/{id}/toggle', [ProgramChairController::class, 'toggleAlumni'])->name('alumni.toggle');
    });

    // Faculty Routes
    Route::prefix('faculty')->name('faculty.')->middleware('faculty')->group(function () {
        Route::get('/dashboard', [FacultyDashboardController::class, 'index'])->name('dashboard');
        Route::get('/test-bank', [TestBankController::class, 'index'])->name('test-bank');
        Route::get('/test-bank/export', [TestBankController::class, 'export'])->name('test-bank.export');
        Route::get('/test-bank/create', [TestBankController::class, 'create'])->name('question.create');
        Route::post('/test-bank', [TestBankController::class, 'store'])->name('question.store');
        Route::get('/test-bank/{id}/edit', [TestBankController::class, 'edit'])->name('question.edit');
        Route::put('/test-bank/{id}', [TestBankController::class, 'update'])->name('question.update');
        Route::delete('/test-bank/{id}', [TestBankController::class, 'destroy'])->name('question.destroy');

        // Question variants (alternative wordings)
        Route::get('/test-bank/{id}/variants', [TestBankController::class, 'variants'])->name('question.variants');
        Route::post('/test-bank/{id}/variants', [TestBankController::class, 'storeVariant'])->name('question.variants.store');
        Route::post('/test-bank/{id}/variants/{variantId}/toggle', [TestBankController::class, 'toggleVariant'])->name('question.variants.toggle');
        Route::delete('/test-bank/{id}/variants/{variantId}', [TestBankController::class, 'destroyVariant'])->name('question.variants.destroy');
        Route::post('/test-bank/{id}/variants/suggest', [TestBankController::class, 'suggestVariant'])->name('question.variants.suggest');
        Route::get('/subjects', fn() => view('faculty.subjects'))->name('subjects');

        // Learning Materials (upload PDFs, Word, PPT, links per topic for students)
        Route::get('/materials', [MaterialController::class, 'index'])->name('materials');
        Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
        Route::get('/performance', [FacultyPerformanceController::class, 'index'])->name('performance');
        Route::get('/performance/export', [FacultyPerformanceController::class, 'export'])->name('performance.export');
        Route::post('/performance/remind', [FacultyPerformanceController::class, 'sendReminder'])->name('performance.remind');
        Route::get('/reports', [FacultyReportController::class, 'index'])->name('reports');
        Route::get('/reports/export', [FacultyReportController::class, 'export'])->name('reports.export');
    });

    // Alumni Routes
    Route::prefix('alumni')->name('alumni.')->middleware('alumni')->group(function () {
        Route::get('/profile', [AlumniProfileController::class, 'edit'])->name('profile');
        Route::post('/profile', [AlumniProfileController::class, 'update'])->name('profile.update');
    });

    // Alumni Community — shared "group page" feed for alumni + students
    Route::get('/community', [CommunityController::class, 'index'])->name('community.index');
    Route::post('/community/posts', [CommunityController::class, 'store'])->name('community.posts.store');
    Route::delete('/community/posts/{post}', [CommunityController::class, 'destroy'])->name('community.posts.destroy');
    Route::post('/community/posts/{post}/like', [CommunityController::class, 'toggleLike'])->name('community.posts.like');
    Route::post('/community/posts/{post}/comments', [CommunityController::class, 'storeComment'])->name('community.comments.store');
    Route::delete('/community/comments/{comment}', [CommunityController::class, 'destroyComment'])->name('community.comments.destroy');
    Route::get('/community/attachments/{attachment}/download', [CommunityController::class, 'download'])->name('community.attachments.download');

    // Resource Library — persistent, filterable study materials, decoupled from the feed
    Route::get('/community/resources', [CommunityResourceController::class, 'index'])->name('community.resources.index');
    Route::post('/community/resources', [CommunityResourceController::class, 'store'])->name('community.resources.store');
    Route::get('/community/resources/{resource}/download', [CommunityResourceController::class, 'download'])->name('community.resources.download');
    Route::delete('/community/resources/{resource}', [CommunityResourceController::class, 'destroy'])->name('community.resources.destroy');

    // Messenger-style chat — the default community GC, alumni-created group chats, and DMs
    Route::get('/messages', [ChatController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [ChatController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [ChatController::class, 'send'])->name('messages.send');
    Route::get('/messages/{conversation}/poll', [ChatController::class, 'poll'])->name('messages.poll');
    Route::post('/messages/start/direct', [ChatController::class, 'startDirect'])->name('messages.start');
    Route::post('/messages/start/group', [ChatController::class, 'createGroup'])->name('messages.group.create');
    Route::post('/messages/{conversation}/members', [ChatController::class, 'addMembers'])->name('messages.members.add');
    Route::put('/messages/{conversation}/rename', [ChatController::class, 'rename'])->name('messages.rename');
    Route::post('/messages/{conversation}/leave', [ChatController::class, 'leave'])->name('messages.leave');
    Route::get('/messages/attachments/{message}/download', [ChatController::class, 'download'])->name('messages.attachments.download');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects');
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::get('/subjects/{subject}/topics/{topic}', [SubjectController::class, 'topic'])->name('subjects.topic');
    Route::get('/materials/{material}/download', [SubjectController::class, 'download'])->name('materials.download');
    Route::get('/adaptive-quizzes', [QuizController::class, 'index'])->name('adaptive-quizzes');

    // Quiz engine
    Route::get('/quiz/history', [QuizController::class, 'history'])->name('quiz.history');
    Route::post('/quiz/start', [QuizController::class, 'start'])->name('quiz.start');
    Route::post('/quiz/{session}/cancel', [QuizController::class, 'cancel'])->name('quiz.cancel');
    Route::get('/quiz/{session}/take', [QuizController::class, 'take'])->name('quiz.take');
    Route::post('/quiz/{session}/submit', [QuizController::class, 'submit'])->name('quiz.submit');
    Route::get('/quiz/{session}/results', [QuizController::class, 'results'])->name('quiz.results');
    // Mock exams are locked until faculty hands out the access code.
    Route::get('/mock-exams', [MockExamController::class, 'index'])->name('mock-exams');
    Route::post('/mock-exams/unlock', [MockExamController::class, 'unlock'])->name('mock-exams.unlock');
    Route::get('/mock-exams/simulation', [MockExamController::class, 'simulation'])->name('mock-exams.simulation');
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance');
    // Review Notes (personal study notes, real CRUD backed by the database)
    Route::get('/review-notes', [ReviewNoteController::class, 'index'])->name('review-notes');
    Route::post('/review-notes', [ReviewNoteController::class, 'store'])->name('review-notes.store');
    Route::get('/review-notes/{note}', [ReviewNoteController::class, 'show'])->name('review-notes.show');
    Route::put('/review-notes/{note}', [ReviewNoteController::class, 'update'])->name('review-notes.update');
    Route::delete('/review-notes/{note}', [ReviewNoteController::class, 'destroy'])->withTrashed()->name('review-notes.destroy');
    Route::post('/review-notes/{note}/favorite', [ReviewNoteController::class, 'favorite'])->name('review-notes.favorite');
    Route::post('/review-notes/{note}/archive', [ReviewNoteController::class, 'archive'])->name('review-notes.archive');
    Route::post('/review-notes/{note}/restore', [ReviewNoteController::class, 'restore'])->withTrashed()->name('review-notes.restore');
    // AI Tutor chat (floating widget on Review Notes)
    Route::post('/ai-tutor/chat', [AiTutorController::class, 'chat'])->middleware('throttle:20,1')->name('ai-tutor.chat');
    Route::get('/ai-tutor/performance-insights', [AiTutorController::class, 'performanceInsights'])->middleware('throttle:10,1')->name('ai-tutor.performance-insights');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::post('/calendar/plan', [CalendarController::class, 'storePlan'])->name('calendar.plan.store');
    Route::delete('/calendar/plan/{id}', [CalendarController::class, 'destroyPlan'])->name('calendar.plan.destroy');
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements');
    Route::get('/settings', function () {
        return view('student.settings');
    })->name('settings');
});
