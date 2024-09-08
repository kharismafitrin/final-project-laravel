<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_new_category()
    {
        $response = $this->postJson('/api/categories', [
            'name' => 'New Category',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Category created successfully',
                'data' => [
                    'name' => 'New Category',
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_create_category_without_name()
    {
        $response = $this->postJson('/api/categories', []);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_retrieves_all_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ]);
    }

    /** @test */
    public function it_retrieves_a_single_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Category retrieved successfully',
                'data' => [
                    'name' => $category->name,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_retrieve_nonexistent_category()
    {
        $response = $this->getJson('/api/categories/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Category not found',
            ]);
    }

    /** @test */
    public function it_updates_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Category',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Category updated successfully',
                'data' => [
                    'name' => 'Updated Category',
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_update_nonexistent_category()
    {
        $response = $this->putJson('/api/categories/999', [
            'name' => 'Updated Category',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Category not found',
            ]);
    }

    /** @test */
    public function it_fails_to_update_category_with_invalid_name()
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => '', // Invalid name
        ]);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_deletes_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Category deleted successfully',
            ]);
    }

    /** @test */
    public function it_fails_to_delete_nonexistent_category()
    {
        $response = $this->deleteJson('/api/categories/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Category not found',
            ]);
    }
}