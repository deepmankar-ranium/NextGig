<?php

namespace Database\Factories;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\JobSeeker;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'jobListing_id' => JobListing::factory(),
            'jobSeeker_id' => JobSeeker::factory(),
            'resume_text' => $this->faker->paragraphs(3, true),
            'cover_letter' => $this->faker->paragraphs(5, true),
        ];
    }
}
