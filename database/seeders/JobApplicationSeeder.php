<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;

class JobApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // Create 50 dummy applications with resumes
        Application::factory()
            ->count(50)
            ->create();
    }
}
