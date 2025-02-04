<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class JobListing extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'salary', 'employer_id'];

    // Define the inverse relationship: Many JobListings belong to one Employer
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    // Define the relationship with tags
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'job_listing_tag');
    }
}
