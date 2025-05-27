<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'challenge_id',
        'code',
        'status',
        'test_output',
        'error_message',
        'execution_time'
    ];

    protected $casts = [
        'execution_time' => 'integer'
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Check if submission passed
     */
    public function isPassed(): bool
    {
        return $this->status === 'passed';
    }

    /**
     * Scope for passed submissions
     */
    public function scopePassed($query)
    {
        return $query->where('status', 'passed');
    }
}