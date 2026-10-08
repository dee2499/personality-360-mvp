<?php

use App\Models\Question;
use App\Models\Survey;
use App\Services\ChangeQuotientQuestionService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultQuestions = ChangeQuotientQuestionService::getDefaultQuestions();

        foreach (Survey::all() as $survey) {
            foreach ($defaultQuestions as $index => $dq) {
                $sort = $index + 1;
                $question = Question::where('survey_id', $survey->id)
                    ->where('sort_order', $sort)
                    ->first();

                if ($question) {
                    $question->update([
                        'question_text' => $dq['question_text'],
                        'peer_question_text' => $dq['peer_question_text'] ?? null,
                        'min_score_description' => $dq['min_score_description'],
                        'max_score_description' => $dq['max_score_description'],
                        'dimension' => $dq['dimension'],
                        'type' => $dq['type'],
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
