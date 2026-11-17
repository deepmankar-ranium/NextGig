<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory;

    protected $table = 'applications';

    protected $fillable = [
        'jobListing_id', 
        'jobSeeker_id', 
        'resume_text', 
        'cover_letter', 
        'application_status'
    ];

    protected $attributes = [
        'application_status' => self::STATUS_APPLIED,
    ];

    // Pipeline stages, in order. Rejected can happen from any open stage.
    const STATUS_APPLIED = 'applied';
    const STATUS_VIEWED = 'viewed';
    const STATUS_SHORTLISTED = 'shortlisted';
    const STATUS_INTERVIEW = 'interview';
    const STATUS_OFFER = 'offer';
    const STATUS_REJECTED = 'rejected';

    /** Stages the seeker gets an email for. */
    const EMAIL_STAGES = [self::STATUS_SHORTLISTED, self::STATUS_INTERVIEW, self::STATUS_OFFER];

    /**
     * @return list<string>
     */
    public static function getApplicationStatuses(): array
    {
        return [...self::pipeline(), self::STATUS_REJECTED];
    }

    /**
     * @return list<string>
     */
    public static function pipeline(): array
    {
        return [self::STATUS_APPLIED, self::STATUS_VIEWED, self::STATUS_SHORTLISTED, self::STATUS_INTERVIEW, self::STATUS_OFFER];
    }

    public function isClosed(): bool
    {
        return in_array($this->application_status, [self::STATUS_OFFER, self::STATUS_REJECTED], true);
    }

    /**
     * Forward moves (skipping is fine) and rejecting an open application are always allowed.
     * Anything else — going back a stage, or reopening an offer/rejection — needs $reopen,
     * an explicit choice by the employer.
     */
    public function canTransitionTo(string $to, bool $reopen = false): bool
    {
        $from = $this->application_status;

        if ($to === $from || ! in_array($to, self::getApplicationStatuses(), true)) {
            return false;
        }

        if ($reopen) {
            return true;
        }

        if ($to === self::STATUS_REJECTED) {
            return ! $this->isClosed();
        }

        $order = array_flip(self::pipeline());

        return ! $this->isClosed() && isset($order[$from]) && $order[$to] > $order[$from];
    }

    /**
     * Relationships
     */
    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'jobListing_id');
    }

    public function jobSeeker()
    {
        return $this->belongsTo(Jobseeker::class, 'jobSeeker_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(ApplicationStatusChange::class)->orderBy('id');
    }

    /**
     * The only way to change a status: always records who did it and when.
     */
    public function transitionTo(string $status, ?User $actor): void
    {
        $from = $this->exists ? $this->getOriginal('application_status') : null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($status, $actor, $from) {
            $this->application_status = $status;
            $this->save();

            $this->statusHistory()->create([
                'from_status' => $from,
                'to_status' => $status,
                'changed_by' => $actor?->id,
            ]);
        });
    }
}
