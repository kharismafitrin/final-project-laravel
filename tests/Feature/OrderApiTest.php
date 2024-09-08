<?php
namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_an_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_create_order_with_invalid_product()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/orders', [
            'product_id' => 999, // Invalid product ID
            'quantity' => 2,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Product Not Found!',
            ]);
    }

    /** @test */
    public function it_retrieves_all_orders_for_logged_in_user()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'quantity',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_retrieves_a_single_order_for_logged_in_user()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user, 'api')->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Order retrieved successfully',
                'data' => [
                    'id' => $order->id,
                    'product_id' => $order->product_id,
                    'quantity' => $order->quantity,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_retrieve_order_if_not_authorized()
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /** @test */
    public function it_fails_to_retrieve_nonexistent_order()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->getJson('/api/orders/999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Order Not Found',
            ]);
    }

    /** @test */
    public function it_updates_an_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user, 'api')->putJson("/api/orders/{$order->id}", [
            'quantity' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Order updated successfully',
                'data' => [
                    'id' => $order->id,
                    'quantity' => 3,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_update_order_if_not_authorized()
    {
        $order = Order::factory()->create();

        $response = $this->putJson("/api/orders/{$order->id}", [
            'quantity' => 3,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /** @test */
    public function it_fails_to_update_nonexistent_order()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->putJson("/api/orders/999", [
            'quantity' => 3,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Order Not Found',
            ]);
    }



    /** @test */
    public function it_deletes_an_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user, 'api')->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Order deleted successfully',
            ]);
    }

    /** @test */
    public function it_fails_to_delete_order_if_not_authorized()
    {
        $order = Order::factory()->create();

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /** @test */
    public function it_fails_to_delete_nonexistent_order()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')->deleteJson("/api/orders/999");

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Order Not Found',
            ]);
    }

    /** @test */
    public function it_generates_user_report()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Simulate creating an order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 2 * $product->price,
            'order_date' => now(),
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/orders/myreport');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Order report generated successfully',
                'data' => [
                    'customer_name' => $user->name,
                    'customer_address' => $user->address,
                    'total_orders' => $order->count(),
                    'total_price' => $order->sum('total_price'),
                    'orders' => [
                        [
                            'id' => $order->id,
                            'product_name' => $product->name,
                            'category_name' => $product->category->name,
                            'quantity' => 2,
                            'total_price' => $order->quantity * $product->price,
                            'order_date' => $order->order_date,
                        ]
                    ],
                ],
            ]);
    }




    /** @test */
    public function it_generates_report_for_all_orders()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 2 * $product->price,
            'order_date' => now(), // Ensure this is a valid datetime
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/orders/report');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Order report generated successfully',
                'data' => [
                    'total_orders' => $order->count(),
                    'total_revenue' => $order->sum('total_price'),
                    // Ensure the format is correct
                    'orders' => [
                        [
                            'id' => $order->id, // Use the actual order ID
                            'product_name' => $product->name,
                            'category_name' => $product->category->name,
                            'quantity' => $order->quantity,
                            'total_price' => $order->total_price, // Ensure the format is correct
                            'customer_name' => $user->name,
                            'customer_address' => $user->address,
                            'order_date' => $order->order_date, // Ensure the format is correct
                        ],
                    ],
                ],
            ]);
    }


}