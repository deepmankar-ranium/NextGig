<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Application extends Model
{
    use HasFactory;

    protected $table = 'applications';
    protected $fillable = ['jobListing_id', 'jobSeeker_id', 'resume_text','cover_letter'];

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'jobListing_id');
    }

    public function jobSeeker()
    {
        return $this->belongsTo(JobSeeker::class,'jobSeeker_id');
    }
}
