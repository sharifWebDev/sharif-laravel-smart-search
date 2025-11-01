<?php

namespace Sharif\LaravelSmartSearch\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Sharif\LaravelSmartSearch\Tests\Models\Product;
use Sharif\LaravelSmartSearch\Tests\Models\Category;
use Sharif\LaravelSmartSearch\Tests\Models\User;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'sku' => strtoupper($this->faker->bothify('???-####')),
            'description' => $this->faker->sentence(),
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'status' => 'active',
        ];
    }
}
