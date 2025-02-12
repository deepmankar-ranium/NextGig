<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Role;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployerFactory extends Factory
{
    protected $model = Employer::class;

    public function definition()
    {
        // Get the Employer role
        $employerRole = Role::where('name', 'Employer')->first();

        // Create a user with Employer role
        $user = User::factory()->create([
            'role_id' => $employerRole->id
        ]);

        return [
            'user_id' => $user->id,
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'description' => fake()->paragraphs(2, true),
        ];
    }
}