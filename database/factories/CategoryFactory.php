<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name'        => ucwords($name),
            'slug'        => Str::slug($name),
            'description' => $this->faker->optional()->sentence(),
            'parent_id'   => null,
            'image'       => null,
            'is_active'   => true,
            'sort_order'  => $this->faker->numberBetween(0, 100),
        ];
    }

    // -------------------------------------------------------------------------
    // States
    // -------------------------------------------------------------------------

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * Create as a sub-category of an existing or new parent.
     */
    public function withParent(?Category $parent = null): static
    {
        return $this->state(function () use ($parent) {
            $parentId = $parent?->id ?? Category::factory()->create()->id;

            return ['parent_id' => $parentId];
        });
    }
}
