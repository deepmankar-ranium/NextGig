<?php 
namespace Database\Seeders;

use App\Models\JobListing;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class JobListingTagSeeder extends Seeder
{
    public function run()
    {
        // Create 20 job listings and attach random tags using the factory
        JobListing::factory()
            ->count(10)
            ->create()
            ->each(function ($jobListing) {
                // Attach between 1 to 3 random tags to each job listing
                $tags = Tag::inRandomOrder()->take(rand(1, 3))->get();
                $jobListing->tags()->attach($tags);
            });
    }
}
