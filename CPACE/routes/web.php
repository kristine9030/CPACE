<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
    Route::post('/signup', [AuthController::class, 'signup']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Faculty Routes
    Route::prefix('faculty')->name('faculty.')->middleware('faculty')->group(function () {
        Route::get('/dashboard', fn() => view('faculty.dashboard'))->name('dashboard');
        Route::get('/test-bank', fn() => view('faculty.test-bank'))->name('test-bank');
        Route::get('/test-bank/create', fn() => view('faculty.question-form'))->name('question.create');
        Route::get('/test-bank/{id}/edit', fn($id) => view('faculty.question-form', ['editMode' => true]))->name('question.edit');
        Route::get('/subjects', fn() => view('faculty.subjects'))->name('subjects');
        Route::get('/performance', fn() => view('faculty.performance'))->name('performance');
    });
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/subjects', function () {
        return view('subjects');
    })->name('subjects');
    Route::get('/adaptive-quizzes', function () {
        return view('adaptive-quizzes');
    })->name('adaptive-quizzes');
    Route::get('/mock-exams', function () {
        return view('mock-exams');
    })->name('mock-exams');
    Route::get('/performance', function () {
        return view('performance');
    })->name('performance');
    Route::get('/review-notes', function () {
        return view('review-notes');
    })->name('review-notes');
    Route::get('/calendar', function () {
        return view('calendar');
    })->name('calendar');
    Route::get('/achievements', function () {
        return view('achievements');
    })->name('achievements');
});
