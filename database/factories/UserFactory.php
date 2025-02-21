<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Role;

class UserFactory extends Factory
{
    protected static ?string $password;

    protected function ensureRolesExist()
    {
        if (Role::count() === 0) {
            Role::create(['name' => 'Admin']);
            Role::create(['name' => 'Employer']);
            Role::create(['name' => 'Job Seeker']);
        }
    }

    public function definition(): array
    {
        $this->ensureRolesExist();

        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => Role::inRandomOrder()->first()->id,
            'google_id' => fake()->optional()->numerify('#############################'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
