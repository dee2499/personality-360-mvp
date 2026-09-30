<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    /**
     * Determine whether the user can view any assessments.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the assessment.
     * Admin can view all.
     * Participants can only view assessments where they are the assessor.
     */
    public function view(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $assessment->assessor_id;
    }

    /**
     * Determine whether the user can answer / update the assessment.
     */
    public function update(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $assessment->assessor_id && ! $assessment->isCompleted();
    }

    /**
     * Determine whether the user can view specific answers submitted.
     */
    public function viewAnswers(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $assessment->assessor_id;
    }
}
