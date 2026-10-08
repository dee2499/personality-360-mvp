<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'username',
        'email',
        'password',
        'role',
        'invitation_token',
        'invitation_sent_at',
        'invitation_accepted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'invitation_sent_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getFirstNameAttribute(): string
    {
        $parts = explode(' ', trim($this->name ?? ''), 2);

        return $parts[0] ?? '';
    }

    public function getLastNameAttribute(): string
    {
        $parts = explode(' ', trim($this->name ?? ''), 2);

        return $parts[1] ?? '';
    }

    public function isInvited(): bool
    {
        return ! empty($this->invitation_token) && empty($this->invitation_accepted_at);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'manager'], true);
    }

    public function isParticipant(): bool
    {
        return $this->role === 'participant';
    }

    public function assessmentsGiven(): HasMany
    {
        return $this->hasMany(Assessment::class, 'assessor_id');
    }

    public function assessmentsReceived(): HasMany
    {
        return $this->hasMany(Assessment::class, 'subject_id');
    }

    public function surveys(): BelongsToMany
    {
        return $this->belongsToMany(Survey::class, 'survey_participants');
    }
}
