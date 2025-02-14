<?php

namespace Database\Seeders;


namespace Database\Seeders;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            JobListingSeeder::class, 
            TagSeeder::class,
            JobListingTagSeeder::class,
            JobseekerSeeder::class,
            EmployerSeeder::class,
            JobApplicationSeeder::class,
          
        ]);
    }
}

