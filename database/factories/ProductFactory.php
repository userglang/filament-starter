<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name  = $this->faker->unique()->words(3, true);
        $price = $this->faker->randomFloat(2, 10, 5000);

        return [
            'category_id'         => Category::factory(),
            'name'                => ucwords($name),
            'slug'                => Str::slug($name),
            'short_description'   => $this->faker->optional()->sentence(),
            'description'         => $this->faker->optional()->paragraphs(3, true),
            'sku'                 => strtoupper($this->faker->unique()->bothify('SKU-#####')),
            'barcode'             => $this->faker->optional()->ean13(),
            'price'               => $price,
            'compare_price'       => $this->faker->optional(0.4)->randomFloat(2, $price, $price * 1.5),
            'cost_price'          => $this->faker->optional(0.6)->randomFloat(2, 1, $price),
            'stock_quantity'      => $this->faker->numberBetween(0, 500),
            'low_stock_threshold' => 5,
            'track_quantity'      => true,
            'is_active'           => true,
            'is_featured'         => false,
            'image'               => null,
            'images'              => null,
            'meta'                => null,
            'weight'              => $this->faker->optional()->randomFloat(2, 0.1, 50),
            'weight_unit'         => 'kg',
            'sort_order'          => $this->faker->numberBetween(0, 100),
        ];
    }

    // -------------------------------------------------------------------------
    // States
    // -------------------------------------------------------------------------

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'];

            return [
                'compare_price' => round($price * $this->faker->randomFloat(2, 1.1, 1.5), 2),
            ];
        });
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'stock_quantity' => 0,
            'track_quantity' => true,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'stock_quantity'      => $this->faker->numberBetween(1, 5),
            'low_stock_threshold' => 5,
            'track_quantity'      => true,
        ]);
    }

    public function untracked(): static
    {
        return $this->state(fn () => [
            'track_quantity' => false,
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Assign to an existing category instead of creating a new one.
     */
    public function forCategory(Category $category): static
    {
        return $this->state(fn () => ['category_id' => $category->id]);
    }

    /**
     * Include SEO meta data.
     */
    public function withMeta(): static
    {
        return $this->state(fn () => [
            'meta' => [
                'title'       => $this->faker->sentence(5),
                'description' => $this->faker->sentence(15),
                'keywords'    => implode(', ', $this->faker->words(5)),
            ],
        ]);
    }

    /**
     * Include a gallery of image paths.
     */
    public function withImages(int $count = 3): static
    {
        return $this->state(fn () => [
            'image'  => "products/main-{$this->faker->uuid()}.jpg",
            'images' => collect(range(1, $count))
                ->map(fn () => "products/gallery-{$this->faker->uuid()}.jpg")
                ->all(),
        ]);
    }
}
