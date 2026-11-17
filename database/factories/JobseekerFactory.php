<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Role;
use App\Models\Jobseeker;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobseekerFactory extends Factory
{
    protected $model = Jobseeker::class;

    public function definition()
    {
        // Get the Job Seeker role ID
        $jobSeekerRole = Role::where('name', 'Job Seeker')->first();

        // Create a user with Job Seeker role
        $user = User::factory()->create([
            'role_id' => $jobSeekerRole->id
        ]);

        return [
            'user_id' => $user->id,
            'resume' => fake()->filePath(), // Simulating a resume file path
            'email'=>fake()->email(),
            'skills' => implode(',', fake()->randomElements([
                'PHP', 'Laravel', 'JavaScript', 'React', 'Vue.js', 
                'Python', 'Java', 'SQL', 'Node.js', 'Docker'
            ], rand(3, 6))),
        ];
    }
}