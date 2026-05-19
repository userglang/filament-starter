<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // Fixed branches for consistent reference across seeders
        $branches = [
            ['branch_number' => 'BR-0001', 'branch_name' => 'Main Branch',  'code' => 'MAIN', 'address' => '123 Main Street, Makati City'],
            ['branch_number' => 'BR-0002', 'branch_name' => 'North Branch', 'code' => 'NORTH', 'address' => '45 North Ave, Quezon City'],
            ['branch_number' => 'BR-0003', 'branch_name' => 'South Branch', 'code' => 'SOUTH', 'address' => '78 South Road, Muntinlupa City'],
        ];

        foreach ($branches as $data) {
            Branch::firstOrCreate(
                ['branch_number' => $data['branch_number']],
                array_merge($data, ['is_active' => true])
            );
        }

        // Additional random branches
        Branch::factory(5)->create();

        // A couple of inactive ones
        Branch::factory(2)->inactive()->create();

        $this->command->info('Branches seeded.');
    }
}
