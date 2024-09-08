<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_new_product()
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'category_id' => $category->id,
            'price' => 99.99,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => [
                    'name' => 'New Product',
                    'price' => 99.99,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_create_product_without_name()
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/products', [
            'category_id' => $category->id,
            'price' => 99.99,
        ]);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_fails_to_create_product_with_invalid_category()
    {
        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'category_id' => 999, // Non-existent category
            'price' => 99.99,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Category not found!'
            ]);
    }

    /** @test */
    public function it_fails_to_create_product_with_invalid_price()
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'category_id' => $category->id,
            'price' => 'invalid_price', // Invalid price
        ]);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function it_retrieves_all_products()
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'category_id', 'price'],
                ],
            ]);
    }

    /** @test */
    public function it_retrieves_a_single_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product retrieved successfully',
                'data' => [
                    'name' => $product->name,
                    'price' => $product->price,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_retrieve_nonexistent_product()
    {
        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Product not found',
            ]);
    }

    /** @test */
    public function it_updates_a_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Product',
            'category_id' => $category->id,
            'price' => 129.99,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product updated successfully',
                'data' => [
                    'name' => 'Updated Product',
                    'price' => 129.99,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_update_nonexistent_product()
    {
        $category = Category::factory()->create();

        $response = $this->putJson('/api/products/999', [
            'name' => 'Updated Product',
            'category_id' => $category->id,
            'price' => 129.99,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Product not found',
            ]);
    }

    /** @test */
    public function it_fails_to_update_product_with_invalid_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Product',
            'category_id' => 999, // Non-existent category
            'price' => 129.99,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Category not found!'
            ]);
    }

    /** @test */
    public function it_deletes_a_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Product deleted successfully',
            ]);
    }

    /** @test */
    public function it_fails_to_delete_nonexistent_product()
    {
        $response = $this->deleteJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Product not found',
            ]);
    }


}
