<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'role' => UserRole::DataEntry,
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Administrator,
        ]);
    }

    public function dataEntry(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::DataEntry,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Inactive,
        ]);
    }
}
