<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class JobListing extends Model
{
    use HasFactory;



    protected $fillable = ['title', 'description', 'salary', 'employer_id'];

   
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'job_listing_tag');
    }
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
