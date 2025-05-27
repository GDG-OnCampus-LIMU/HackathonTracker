<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'phase',
        'points',
        'order_in_phase',
        'test_script',
        'expected_output',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
        'order_in_phase' => 'integer'
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}