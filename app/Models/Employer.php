<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'description'];

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

