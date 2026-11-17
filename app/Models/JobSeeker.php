<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jobseeker extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'resume', 'skills', 'email', 'resume_text', 'profile', 'profile_updated_at'];

    /**
     * Resume file/text and the parsed profile are private to the seeker; employers get
     * match data through the ranking service instead.
     */
    protected $hidden = ['resume', 'resume_text', 'profile'];

    protected $casts = [
        'profile' => 'array',
        'profile_updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'jobSeeker_id');
    }

    /**
     * Normalised skills from the parsed profile, falling back to the legacy comma list.
     *
     * @return list<string>
     */
    public function profileSkills(): array
    {
        $skills = $this->profile['skills'] ?? array_filter(array_map('trim', explode(',', (string) $this->skills)));

        return array_values($skills);
    }
}
