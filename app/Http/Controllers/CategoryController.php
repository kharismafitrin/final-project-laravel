<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Middleware\AcceptJsonMiddleware;

class CategoryController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(AcceptJsonMiddleware::class);
    // }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'status' => 'Success',
            'message' => 'Categories retrieved successfully',
            'data' => $categories
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
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $category = Category::create($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::find($id);
        if (empty($category)) {
            return response()->json([
                "status" => "error",
                "message" => "Category not found"
            ], 404);
        }
        return response()->json([
            "status" => "success",
            "message" => "Category retrieved successfully",
            "data" => $category
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (empty($category)) {
            return response()->json([
                "status" => "error",
                "message" => "Category not found"
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update($request->all());

        return response()->json([
            "status" => "success",
            "message" => "Category updated successfully",
            "data" => $category
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if (empty($category)) {
            return response()->json([
                "status" => "error",
                "message" => "Category not found"
            ], 404);
        }

        if ($category->products()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete category because it is associated with existing orders'
            ], 400);
        }

        $category->delete();
        return response()->json([
            "status" => "success",
            "message" => "Category deleted successfully",
            "data" => $category
        ], 200);
    }
}
