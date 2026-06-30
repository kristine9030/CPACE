<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\QuizApiController;
use App\Http\Controllers\Api\PerformanceApiController;
use App\Http\Controllers\Api\ReviewNoteApiController;
use App\Http\Controllers\Api\CalendarApiController;
use App\Http\Controllers\Api\SubjectsApiController;

// ── Public (no token required) ────────────────────────────────────────────
Route::post('/login',  [AuthApiController::class, 'login']);
Route::post('/signup', [AuthApiController::class, 'signup']);

// ── Authenticated student routes ──────────────────────────────────────────
Route::middleware('api.auth')->group(function () {

    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user',    [AuthApiController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    // Subjects list
    Route::get('/subjects', [SubjectsApiController::class, 'index']);

    // Quiz engine
    Route::get('/quizzes',              [QuizApiController::class, 'index']);
    Route::get('/quizzes/history',      [QuizApiController::class, 'history']);
    Route::post('/quizzes/start',       [QuizApiController::class, 'start']);
    Route::get('/quizzes/{session}',    [QuizApiController::class, 'take']);
    Route::post('/quizzes/{session}/submit', [QuizApiController::class, 'submit']);
    Route::post('/quizzes/{session}/cancel', [QuizApiController::class, 'cancel']);
    Route::get('/quizzes/{session}/results', [QuizApiController::class, 'results']);

    // Performance
    Route::get('/performance', [PerformanceApiController::class, 'index']);

    // Review Notes
    Route::get('/review-notes',            [ReviewNoteApiController::class, 'index']);
    Route::post('/review-notes',           [ReviewNoteApiController::class, 'store']);
    Route::get('/review-notes/{note}',     [ReviewNoteApiController::class, 'show']);
    Route::put('/review-notes/{note}',     [ReviewNoteApiController::class, 'update']);
    Route::delete('/review-notes/{note}',  [ReviewNoteApiController::class, 'destroy']);
    Route::post('/review-notes/{note}/favorite', [ReviewNoteApiController::class, 'favorite']);

    // Spaced repetition calendar
    Route::get('/calendar', [CalendarApiController::class, 'index']);
});
