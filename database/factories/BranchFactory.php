<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $city = $this->faker->unique()->city();
        $code = strtoupper(Str::slug($city, ''));
        $code = substr($code, 0, 6); // keep it short

        return [
            'branch_number' => strtoupper($this->faker->unique()->bothify('BR-####')),
            'branch_name'   => $city . ' Branch',
            'address'       => $this->faker->address(),
            'code'          => $code,
            'is_active'     => true,
        ];
    }

    // -------------------------------------------------------------------------
    // States
    // -------------------------------------------------------------------------

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
