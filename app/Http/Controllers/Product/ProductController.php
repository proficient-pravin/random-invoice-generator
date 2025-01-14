<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        if (request()->ajax()) {
            $products = Product::query();
            return DataTables::of($products)
                ->addColumn('actions', function ($product) {
                    return view('products.actions', compact('product'));
                })
                ->make(true);
        }

        return view('products.index');
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param \App\Models\Product $product
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product from storage.
     *
     * @param \App\Models\Product $product
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }
}
