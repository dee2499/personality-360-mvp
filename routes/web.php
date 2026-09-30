<?php

use App\Http\Controllers\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PeopleController as AdminPeopleController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Admin\SurveyParticipantController as AdminSurveyParticipantController;
use App\Http\Controllers\Admin\SurveyQuestionController as AdminSurveyQuestionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Participant\AssessmentController as ParticipantAssessmentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest / Landing redirect
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('participant.assessments.index');
    }

    return redirect()->route('login');
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Participant Routes
Route::middleware('auth')->group(function () {
    Route::get('/my-assessments', [ParticipantAssessmentController::class, 'index'])
        ->name('participant.assessments.index');

    Route::get('/my-assessments/{assessment}', [ParticipantAssessmentController::class, 'show'])
        ->name('participant.assessments.show');

    Route::post('/my-assessments/{assessment}/submit', [ParticipantAssessmentController::class, 'submit'])
        ->name('participant.assessments.submit');
});

// Admin Routes
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Surveys
        Route::resource('surveys', AdminSurveyController::class);
        Route::post('surveys/{survey}/publish', [AdminSurveyController::class, 'publish'])->name('surveys.publish');
        Route::post('surveys/{survey}/unpublish', [AdminSurveyController::class, 'unpublish'])->name('surveys.unpublish');

        // Survey Questions
        Route::post('surveys/{survey}/questions', [AdminSurveyQuestionController::class, 'store'])->name('surveys.questions.store');
        Route::put('surveys/{survey}/questions/{question}', [AdminSurveyQuestionController::class, 'update'])->name('surveys.questions.update');
        Route::delete('surveys/{survey}/questions/{question}', [AdminSurveyQuestionController::class, 'destroy'])->name('surveys.questions.destroy');

        // Survey Participants
        Route::post('surveys/{survey}/participants', [AdminSurveyParticipantController::class, 'store'])->name('surveys.participants.store');
        Route::delete('surveys/{survey}/participants/{participant}', [AdminSurveyParticipantController::class, 'destroy'])->name('surveys.participants.destroy');

        // People & 360 Profiles
        Route::get('/people', [AdminPeopleController::class, 'index'])->name('people.index');
        Route::get('/people/{person}', [AdminPeopleController::class, 'show'])->name('people.show');

        // Assessments
        Route::get('/assessments', [AdminAssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/{assessment}', [AdminAssessmentController::class, 'show'])->name('assessments.show');
    });
