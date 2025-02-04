<?php 
namespace Database\Factories;

use App\Models\JobListing;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobListingTagFactory extends Factory
{
    public function definition()
    {
        return [
            'job_listing_id' => JobListing::factory(),
            'tag_id' => Tag::factory(),
        ];
    }
}