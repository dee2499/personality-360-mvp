<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\ScoreCategory;
use App\Services\AssessmentCategoryService;
use App\Services\AssessmentScoreService;
use Database\Seeders\ScoreCategorySeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of all score categories.
     */
    public function index(): View
    {
        $categories = ScoreCategory::orderBy('min_percentage')->get();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new score category.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created score category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_percentage'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'color' => ['nullable', 'string', 'max:16', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        if (empty($validated['emoji'])) {
            $validated['emoji'] = '🎯';
        }

        if (empty($validated['color'])) {
            $validated['color'] = '#4F46E5';
        }

        ScoreCategory::create($validated);
        AssessmentCategoryService::clearCache();

        return redirect()->route('admin.categories.index')
            ->with('success', "Score category '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified score category.
     */
    public function edit(ScoreCategory $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified score category in storage.
     */
    public function update(Request $request, ScoreCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'gte:min_percentage'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'color' => ['nullable', 'string', 'max:16', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        if (empty($validated['emoji'])) {
            $validated['emoji'] = '🎯';
        }

        if (empty($validated['color'])) {
            $validated['color'] = '#4F46E5';
        }

        $category->update($validated);
        AssessmentCategoryService::clearCache();

        return redirect()->route('admin.categories.index')
            ->with('success', "Score category '{$category->name}' updated successfully.");
    }

    /**
     * Remove the specified score category from storage.
     */
    public function destroy(ScoreCategory $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();
        AssessmentCategoryService::clearCache();

        return redirect()->route('admin.categories.index')
            ->with('success', "Score category '{$name}' deleted successfully.");
    }

    /**
     * Reset categories back to standard system defaults (Apple, Orange, Tomato, Lemon, Cucumber).
     */
    public function resetDefaults(): RedirectResponse
    {
        ScoreCategory::truncate();
        (new ScoreCategorySeeder)->run();
        AssessmentCategoryService::clearCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Score categories have been reset to default standard ranges (Apple, Orange, Tomato, Lemon, Cucumber).');
    }

    /**
     * Recalculate categories across all completed assessments according to the current rules.
     */
    public function recalculate(AssessmentScoreService $scoreService): RedirectResponse
    {
        AssessmentCategoryService::clearCache();
        $assessments = Assessment::where('status', 'completed')->get();
        $count = 0;

        foreach ($assessments as $assessment) {
            $scoreService->calculateAssessmentScore($assessment);
            $count++;
        }

        return redirect()->route('admin.categories.index')
            ->with('success', "Successfully recalculated categories for {$count} completed assessment(s).");
    }
}
