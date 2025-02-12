<?php

namespace Database\Seeders;

use App\Models\Employer;
use Illuminate\Database\Seeder;

class EmployerSeeder extends Seeder
{
    public function run()
    {
        // Create 5 employers (adjust the number as needed)
        Employer::factory()
            ->count(5)
            ->create();
    }
}