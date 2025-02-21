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
}
