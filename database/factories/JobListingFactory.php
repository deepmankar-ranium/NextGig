<?php

namespace Database\Factories;

use App\Models\JobListing;
use App\Models\Employer;  // Import the Employer model
use Illuminate\Database\Eloquent\Factories\Factory;

class JobListingFactory extends Factory
{
    protected $model = JobListing::class;

    public function definition()
    {
        return [
            'title' => $this->faker->jobTitle(),
            'description' => $this->faker->paragraph(),
            'salary' => $this->faker->randomFloat(2, 50000, 150000),
            'employer_id' => Employer::factory(),  // Create an Employer instance for the relation
        ];
    }
}
