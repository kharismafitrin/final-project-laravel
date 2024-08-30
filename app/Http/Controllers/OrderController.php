<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Product;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::all();
        return response()->json($orders);
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
        $product = Product::find($request->product_id);
        if (empty($product)) {
            return response()->json(["message" => "Product not Found"], 404);
        }
        $totalprice = $product->price * $request->quantity;
        $order = Order::create([
            "user_id" => $request->user_id,
            "product_id" => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $totalprice,
            'order_date' => now(),
        ]);
        return response()->json($order, 200);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::find($id);
        if (empty($order)) {
            return response()->json(['message' => 'Order Not Found'], 404);
        }
        return response()->json($order, 200);
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
        if (empty($order)) {
            return response()->json(['message' => 'Order not Found'], 404);
        }
        $order->update($request->all());
        return response()->json($order, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if (empty($order)) {
            return response()->json(['message' => 'Order not Found'], 404);
        }
        $order->delete();
        return response()->json($order, 200);
    }
}
