<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Product;

class OrderController extends Controller
{
    private function countTotalPrice($productId, $qty)
    {
        $product = Product::findOrFail($productId);
        if (empty($product)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product Not Found'
            ], 404);
        }
        $total_price = $qty * $product->price;
        return $total_price;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::all();
        // ada 2 skenario, jika tidak login anggapannya admin bisa get all order
        //jika ada usernya sendiri, maka akan get order miliknya sendiri
        $user = auth()->user();
        if (!empty($user)) {
            $orders = $orders->where("user_id", $user->id);
            return response()->json([
                'status' => 'success',
                'message' => 'Orders retrieved successfully',
                'data' => $orders
            ], 200);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Orders retrieved successfully',
            'data' => $orders
        ], 200);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (empty(Product::find($request->product_id))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product Not Found!'
            ], 404);
        }

        $totalprice = $this->countTotalPrice($request->product_id, $request->quantity);
        $order = Order::create([
            "user_id" => $user->id,
            "product_id" => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $totalprice,
            'order_date' => now(),
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully',
            'data' => $order
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::find($id);
        $user = auth()->user();
        if (empty($order)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order Not Found'
            ], 404);
        }

        if ($user->id != $order->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Order retrieved successfully',
            'data' => $order
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        $user = auth()->user();
        if (empty($order)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order Not Found'
            ], 404);
        }

        if ($user->id != $order->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $order->update([
            "product_id" => $order->product_id,
            'quantity' => $request->quantity,
            'total_price' => $this->countTotalPrice($order->product_id, $request->quantity)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated successfully',
            'data' => $order
        ], 200);
        ;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        $user = auth()->user();
        if (empty($order)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order Not Found'
            ], 404);
        }

        if ($user->id != $order->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
        $order->delete();
        return response()->json([
            "status" => "success",
            "message" => "Order deleted successfully",
            "data" => $order
        ], 200);
    }

    public function userReport()
    {
        $user = auth()->user();
        $orders = $user->orders()->with('product.category')->get();
        $total_orders = $orders->count();
        $total_price = $orders->sum('total_price');
        $formatted_orders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'product_name' => $order->product->name, // Mengambil nama produk dari relasi
                'category_name' => $order->product->category->name, // Mengakses kategori melalui relasi product
                'quantity' => $order->quantity,
                'total_price' => $order->total_price,
                'order_date' => $order->order_date,
            ];
        });
        $data = [
            'customer_name' => $user->name,
            'customer_address' => $user->address,
            'total_orders' => $total_orders,
            'total_price' => $total_price,
            'orders' => $formatted_orders
        ];
        return response()->json([
            'status' => 'success',
            'message' => 'Order report generated successfully',
            'data' => $data
        ], 200);
    }

    public function report()
    {
        // $user = auth()->user();
        $orders = Order::with('product.category', 'user')->get();
        $total_orders = $orders->count();
        $total_revenue = $orders->sum('total_price');
        $formatted_orders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'product_name' => $order->product->name, // Mengambil nama produk dari relasi
                'category_name' => $order->product->category->name, // Mengakses kategori melalui relasi product
                'quantity' => $order->quantity,
                'total_price' => $order->total_price,
                'customer_name' => $order->user->name,
                'customer_address' => $order->user->address,
                'order_date' => $order->order_date,
            ];
        });
        $data = [
            'total_orders' => $total_orders,
            'total_revenue' => $total_revenue,
            'orders' => $formatted_orders
        ];
        return response()->json([
            'status' => 'success',
            'message' => 'Order report generated successfully',
            'data' => $data
        ]);
    }

}
