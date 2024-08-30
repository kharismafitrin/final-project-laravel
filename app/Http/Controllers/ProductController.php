<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Category;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'price' => 'required|numeric',
        ]);
        if (empty(Category::find($request->category_id))) {
            return response()->json(["message" => "Category not found!"], 404);
        }

        $product = Product::create($request->all());

        return response()->json($product, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::find($id);
        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'price' => 'required|decimal:2',
        ]);
        if (empty(Category::find($request->category_id))) {
            return response()->json(["message" => "Category not found!"], 404);
        }
        $product = Product::find($id);
        if (empty($product)) {
            return response()->json(["message" => "Product not Found"], 404);
        }
        $product->update($request->all());
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        if (empty($product)) {
            return response()->json(["message" => "Product not Found"], 404);
        }
        $product->delete();
        return response()->json(null);
    }
}
