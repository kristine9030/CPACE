<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProgramChairController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\TestBankController;

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

    // Program Chair Routes (Admin role)
    Route::prefix('chair')->name('chair.')->middleware('chair')->group(function () {
        Route::get('/dashboard', [ProgramChairController::class, 'dashboard'])->name('dashboard');

        // Faculty account management
        Route::get('/faculty', [ProgramChairController::class, 'faculty'])->name('faculty');
        Route::get('/faculty/create', [ProgramChairController::class, 'createFaculty'])->name('faculty.create');
        Route::post('/faculty', [ProgramChairController::class, 'storeFaculty'])->name('faculty.store');
        Route::get('/faculty/{id}/edit', [ProgramChairController::class, 'editFaculty'])->name('faculty.edit');
        Route::put('/faculty/{id}', [ProgramChairController::class, 'updateFaculty'])->name('faculty.update');
        Route::post('/faculty/{id}/assign', [ProgramChairController::class, 'assignSubjects'])->name('faculty.assign');
        Route::post('/faculty/{id}/toggle', [ProgramChairController::class, 'toggleFaculty'])->name('faculty.toggle');

        // Subject assignment overview
        Route::get('/subjects', [ProgramChairController::class, 'subjects'])->name('subjects');
    });

    // Faculty Routes
    Route::prefix('faculty')->name('faculty.')->middleware('faculty')->group(function () {
        Route::get('/dashboard', fn() => view('faculty.dashboard'))->name('dashboard');
        Route::get('/test-bank', [TestBankController::class, 'index'])->name('test-bank');
        Route::get('/test-bank/create', [TestBankController::class, 'create'])->name('question.create');
        Route::post('/test-bank', [TestBankController::class, 'store'])->name('question.store');
        Route::get('/test-bank/{id}/edit', [TestBankController::class, 'edit'])->name('question.edit');
        Route::put('/test-bank/{id}', [TestBankController::class, 'update'])->name('question.update');
        Route::delete('/test-bank/{id}', [TestBankController::class, 'destroy'])->name('question.destroy');
        Route::get('/subjects', fn() => view('faculty.subjects'))->name('subjects');
        Route::get('/performance', fn() => view('faculty.performance'))->name('performance');
    });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/subjects', function () {
        return view('student.subjects');
    })->name('subjects');
    Route::get('/adaptive-quizzes', [QuizController::class, 'index'])->name('adaptive-quizzes');

    // Quiz engine
    Route::post('/quiz/start', [QuizController::class, 'start'])->name('quiz.start');
    Route::get('/quiz/{session}/take', [QuizController::class, 'take'])->name('quiz.take');
    Route::post('/quiz/{session}/submit', [QuizController::class, 'submit'])->name('quiz.submit');
    Route::get('/quiz/{session}/results', [QuizController::class, 'results'])->name('quiz.results');
    Route::get('/mock-exams', function () {
        return view('student.mock-exams');
    })->name('mock-exams');
    Route::get('/performance', function () {
        return view('student.performance');
    })->name('performance');
    Route::get('/review-notes', function () {
        return view('student.review-notes');
    })->name('review-notes');
    Route::get('/calendar', function () {
        return view('student.calendar');
    })->name('calendar');
    Route::get('/achievements', function () {
        return view('student.achievements');
    })->name('achievements');
});
