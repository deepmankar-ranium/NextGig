<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Application extends Model
{
    use HasFactory;

    protected $fillable = ['job_id', 'jobseeker_id', 'status'];

    public function job()
    {
        return $this->belongsTo(JobListing::class);
    }

    public function jobseeker()
    {
        return $this->belongsTo(JobSeeker::class);
    }
}
