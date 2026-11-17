<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A user reporting another user from the inbox. Reviewed by an admin (reviewed_at).
 */
class UserReport extends Model
{
    public const REASONS = [
        'spam' => 'Spam or scam',
        'harassment' => 'Harassment or abuse',
        'fake' => 'Fake job or fake profile',
        'other' => 'Something else',
    ];

    protected $fillable = ['reporter_id', 'reported_id', 'reason', 'details'];

    protected $casts = ['reviewed_at' => 'datetime'];
}
