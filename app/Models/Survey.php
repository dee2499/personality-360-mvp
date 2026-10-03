<?php

namespace App\Models;

use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'status',
        'sign_off_status',
        'sign_off_lead',
        'sign_off_notes',
        'signed_off_at',
        'created_by',
        'published_at',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Survey $survey) {
            // Delete all answers belonging to this survey's assessments
            $assessmentIds = $survey->assessments()->pluck('id');
            if ($assessmentIds->isNotEmpty()) {
                AssessmentAnswer::whereIn('assessment_id', $assessmentIds)->delete();
            }

            // Also delete answers linked to this survey's questions
            $questionIds = $survey->questions()->pluck('id');
            if ($questionIds->isNotEmpty()) {
                AssessmentAnswer::whereIn('question_id', $questionIds)->delete();
            }

            // Delete assessments, detach participants, and delete questions
            $survey->assessments()->delete();
            $survey->participants()->detach();
            $survey->questions()->delete();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'signed_off_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function individualQuestions(): HasMany
    {
        return $this->hasMany(Question::class)->where('type', 'individual')->orderBy('sort_order');
    }

    public function groupSyncQuestions(): HasMany
    {
        return $this->hasMany(Question::class)->where('type', 'group_sync')->orderBy('sort_order');
    }

    public function groupSyncAnswers(): HasMany
    {
        return $this->hasMany(GroupSyncAnswer::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'survey_participants');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function completionPercentage(): float
    {
        $total = $this->assessments()->count();
        if ($total === 0) {
            return 0.0;
        }

        $completed = $this->assessments()->where('status', 'completed')->count();

        return round(($completed / $total) * 100, 2);
    }
}
