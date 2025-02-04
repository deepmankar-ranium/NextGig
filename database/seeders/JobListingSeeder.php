<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\JobListing;
use Database\Factories\JobListingFactory;

class JobListingSeeder extends Seeder
{
    public function run()
    {
        // Use the factory class to create 10 job listings
        JobListingFactory::new()->count(10)->create();
    }
}
