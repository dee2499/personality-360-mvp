<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Support\Collection;

class AssessmentGenerationService
{
    /**
     * Generate N x N assessments for all participants attached to the survey.
     * Includes self-assessments and peer-assessments.
     *
     * @return int Number of assessments newly created
     */
    public function generateForSurvey(Survey $survey): int
    {
        $participants = $survey->participants()->get();
        $createdCount = 0;

        foreach ($participants as $assessor) {
            foreach ($participants as $subject) {
                $assessment = Assessment::firstOrCreate(
                    [
                        'survey_id' => $survey->id,
                        'assessor_id' => $assessor->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'status' => 'pending',
                    ]
                );

                if ($assessment->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        }

        return $createdCount;
    }

    /**
     * Generate assessments when participants are passed directly.
     *
     * @param  Collection<int, User>|array<int, User>  $participants
     */
    public function generateForParticipants(Survey $survey, Collection|array $participants): int
    {
        $createdCount = 0;

        foreach ($participants as $assessor) {
            foreach ($participants as $subject) {
                $assessment = Assessment::firstOrCreate(
                    [
                        'survey_id' => $survey->id,
                        'assessor_id' => $assessor->id,
                        'subject_id' => $subject->id,
                    ],
                    [
                        'status' => 'pending',
                    ]
                );

                if ($assessment->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        }

        return $createdCount;
    }
}
