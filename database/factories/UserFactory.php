<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'branch_number'     => null,
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'is_active'         => true,
        ];
    }

    // -------------------------------------------------------------------------
    // States
    // -------------------------------------------------------------------------

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * Assign user to an existing branch, or create one automatically.
     */
    public function forBranch(?Branch $branch = null): static
    {
        return $this->state(function () use ($branch) {
            $branchNumber = $branch?->branch_number ?? Branch::factory()->create()->branch_number;

            return ['branch_number' => $branchNumber];
        });
    }

    /**
     * Set a custom plain-text password (hashed on assignment via model cast).
     */
    public function withPassword(string $password): static
    {
        return $this->state(fn () => ['password' => Hash::make($password)]);
    }
}
