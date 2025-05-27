<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'github_repo',
        'access_token',
        'is_finalist'
    ];

    protected $hidden = [
        'access_token' // Don't expose tokens in API responses
    ];

    protected $casts = [
        'is_finalist' => 'boolean'
    ];

    /**
     * Get all submissions for this team
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get successful submissions only
     */
    public function passedSubmissions(): HasMany
    {
        return $this->hasMany(Submission::class)->where('status', 'passed');
    }

    /**
     * Calculate total score
     */
    public function getTotalScoreAttribute(): int
    {
        return $this->passedSubmissions()
            ->join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
            ->sum('challenges.points');
    }

    /**
     * Get progress by phase
     */
    public function getPhaseProgress(): array
    {
        return $this->passedSubmissions()
            ->join('challenges', 'submissions.challenge_id', '=', 'challenges.id')
            ->selectRaw('challenges.phase, COUNT(*) as completed')
            ->groupBy('challenges.phase')
            ->pluck('completed', 'phase')
            ->toArray();
    }
}