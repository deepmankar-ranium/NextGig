<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name'];

    public function jobListings()
    {
        return $this->belongsToMany(JobListing::class, 'job_listing_tag');
    }
}
