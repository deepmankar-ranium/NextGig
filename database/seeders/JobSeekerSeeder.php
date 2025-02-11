<?php

namespace Database\Seeders;

use App\Models\Jobseeker;
use Illuminate\Database\Seeder;

class JobSeekerSeeder extends Seeder
{
    public function run()
    {
        // Create 10 job seekers (you can adjust the number)
        JobSeeker::factory()
            ->count(10)
            ->create();
    }
}