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
        'application_status' => self::STATUS_PENDING, // Default value
    ];

    // Define application status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get all possible application statuses.
     *
     * @return array
     */
    public static function getApplicationStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];
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
        return $this->belongsTo(JobSeeker::class, 'jobSeeker_id');
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
