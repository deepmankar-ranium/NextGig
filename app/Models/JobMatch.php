<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobMatch extends Model
{
    protected $fillable = ['jobseeker_id', 'job_listing_id', 'score', 'matched', 'missing', 'inputs_hash', 'explanation', 'explanation_hash'];

    protected $casts = [
        'matched' => 'array',
        'missing' => 'array',
        'explanation' => 'array',
        'score' => 'integer',
    ];

    public function jobseeker(): BelongsTo
    {
        return $this->belongsTo(Jobseeker::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function hasFreshExplanation(): bool
    {
        return $this->explanation !== null && $this->explanation_hash === $this->inputs_hash;
    }
}
