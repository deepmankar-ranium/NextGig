<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'email', 'description', 'address', 'phone'];

    // Define the relationship: One Employer has many JobListings
    public function jobListings()
    {
        return $this->hasMany(JobListing::class);
    }
    
    // Define the relationship: One Employer belongs to one User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
