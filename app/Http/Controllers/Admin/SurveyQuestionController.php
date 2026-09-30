<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyQuestionController extends Controller
{
    public function store(Request $request, Survey $survey): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string', 'max:500'],
        ]);

        $maxOrder = (int) $survey->questions()->max('sort_order');

        Question::create([
            'survey_id' => $survey->id,
            'question_text' => trim($validated['question_text']),
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Question added successfully.');
    }

    public function update(Request $request, Survey $survey, Question $question): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $question->update($validated);

        return back()->with('success', 'Question updated successfully.');
    }

    public function destroy(Survey $survey, Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('success', 'Question removed.');
    }
}
