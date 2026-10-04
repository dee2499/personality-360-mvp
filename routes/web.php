<?php

use App\Http\Controllers\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PeopleController as AdminPeopleController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Admin\SurveyParticipantController as AdminSurveyParticipantController;
use App\Http\Controllers\Admin\SurveyQuestionController as AdminSurveyQuestionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Participant\AssessmentController as ParticipantAssessmentController;
use App\Http\Controllers\ProfileController;
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

    // Employee Account Invitation & Activation
    Route::get('/invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/invitation/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Authenticated Routes (Profile & Participant)
Route::middleware('auth')->group(function () {
    // User Profile & Password Update
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Participant 360 Surveys & Assessments
    Route::get('/my-assessments', [ParticipantAssessmentController::class, 'index'])
        ->name('participant.assessments.index');

    // Direct CQ Report User Tab
    Route::get('/my-cq-report', [ParticipantAssessmentController::class, 'showReport'])
        ->name('participant.cq-report');

    // Confidential Individual Change Quotient (CQ 1-3, CQ Sync) Report
    Route::get('/my-assessments/reports/{survey}', [ParticipantAssessmentController::class, 'report'])
        ->name('participant.assessments.report');

    // Participant View of Anonymous Group Insights & Action Recommendations
    Route::get('/my-surveys/{survey}/group-insights', [ParticipantAssessmentController::class, 'groupInsights'])
        ->name('participant.surveys.group-insights');

    // New 11-Question Slider Wizard for Entire Cohort
    Route::get('/my-surveys/{survey}', [ParticipantAssessmentController::class, 'takeSurvey'])
        ->name('participant.surveys.take');
    Route::post('/my-surveys/{survey}/submit', [ParticipantAssessmentController::class, 'submitSurveyMatrix'])
        ->name('participant.surveys.submit-matrix');

    // Individual Assessment View & Direct Submit
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

        // Companies Management (B2B)
        Route::get('companies/{company}/team-sync', [AdminCompanyController::class, 'teamSyncReport'])->name('companies.team-sync');
        Route::resource('companies', AdminCompanyController::class);
        Route::post('companies/{company}/invite', [AdminCompanyController::class, 'inviteEmployee'])->name('companies.invite');
        Route::delete('companies/{company}/employees/{employee}', [AdminCompanyController::class, 'destroyEmployee'])->name('companies.employees.destroy');

        // Surveys
        Route::get('surveys/{survey}/team-sync', [AdminSurveyController::class, 'teamSyncReport'])->name('surveys.team-sync');
        Route::get('surveys/{survey}/group-insights', [AdminSurveyController::class, 'groupInsights'])->name('surveys.group-insights');
        Route::post('surveys/{survey}/sign-off', [AdminSurveyController::class, 'signOff'])->name('surveys.sign-off');
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
        Route::delete('/people/{person}', [AdminPeopleController::class, 'destroy'])->name('people.destroy');

        // Assessments
        Route::get('/assessments', [AdminAssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/{assessment}', [AdminAssessmentController::class, 'show'])->name('assessments.show');

        // Score Categories CRUD
        Route::post('categories/reset-defaults', [AdminCategoryController::class, 'resetDefaults'])->name('categories.reset-defaults');
        Route::post('categories/recalculate', [AdminCategoryController::class, 'recalculate'])->name('categories.recalculate');
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
    });
