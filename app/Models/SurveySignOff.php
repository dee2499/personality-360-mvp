<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveySignOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'user_id',
        'sign_off_lead',
        'status',
        'notes',
        'signed_off_at',
    ];

    protected function casts(): array
    {
        return [
            'signed_off_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
