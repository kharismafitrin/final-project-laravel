<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'category_id' => Category::factory(), // Asosiasi dengan category factory
            'price' => $this->faker->randomFloat(2, 1, 1000), // Random price antara 1 dan 1000
        ];
    }
}