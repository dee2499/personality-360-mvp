<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'survey_id',
        'question_text',
        'type',
        'dimension',
        'min_score_description',
        'max_score_description',
        'peer_question_text',
        'sort_order',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class);
    }

    public function groupSyncAnswers(): HasMany
    {
        return $this->hasMany(GroupSyncAnswer::class);
    }

    public function isIndividual(): bool
    {
        return ($this->type ?? 'individual') === 'individual';
    }

    public function isGroupSync(): bool
    {
        return $this->type === 'group_sync';
    }

    /**
     * Get appropriate prompt depending on whether the subject is self or a peer.
     */
    public function getPromptForAssessor(bool $isSelf): string
    {
        if (! $isSelf && ! empty($this->peer_question_text)) {
            return $this->peer_question_text;
        }

        return $this->question_text;
    }
}
