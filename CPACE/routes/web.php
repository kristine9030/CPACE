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
