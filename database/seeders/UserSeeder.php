<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();

        // Fixed admin user — always available for login
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Admin User',
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active'         => true,
                'branch_number'     => $branches->first()?->branch_number,
            ]
        );

        if ($branches->isEmpty()) {
            // No branches yet — create users without branch assignment
            User::factory(20)->create();
        } else {
            // Distribute users across branches
            $branches->each(function (Branch $branch) {
                User::factory(5)->forBranch($branch)->create();
            });

            // Some users with no branch
            User::factory(5)->create();
        }

        // Unverified users
        User::factory(5)->unverified()->create();

        // Inactive users
        User::factory(3)->inactive()->create();

        $this->command->info('Users seeded.');
    }
}
