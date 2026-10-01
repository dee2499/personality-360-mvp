<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Survey;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalSurveys = Survey::count();
        $activeSurveys = Survey::where('status', 'published')->count();
        $totalParticipants = User::where('role', 'participant')->count();

        $completedAssessments = Assessment::has('survey')->where('status', 'completed')->count();
        $pendingAssessments = Assessment::has('survey')->where('status', 'pending')->count();
        $inProgressAssessments = Assessment::has('survey')->where('status', 'in_progress')->count();
        $totalAssessments = Assessment::has('survey')->count();

        $overallCompletionRate = $totalAssessments > 0
            ? round(($completedAssessments / $totalAssessments) * 100, 1)
            : 0;

        $recentAssessments = Assessment::has('survey')
            ->with(['assessor', 'subject', 'survey'])
            ->latest('updated_at')
            ->take(10)
            ->get();

        $surveys = Survey::withCount(['participants', 'assessments', 'questions'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalSurveys',
            'activeSurveys',
            'totalParticipants',
            'completedAssessments',
            'pendingAssessments',
            'inProgressAssessments',
            'totalAssessments',
            'overallCompletionRate',
            'recentAssessments',
            'surveys'
        ));
    }
}
