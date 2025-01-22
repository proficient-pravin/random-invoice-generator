<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|max:6000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Process the CSV file
            $file = $request->file('import_file');
            $data = array_map('str_getcsv', file($file));
            
            // Extract the header (first row)
            $header = $data[0];
            
            // Map the header with the subsequent rows
            $mappedData = array_filter(array_map(function ($row) use ($header) {                
                if (count($header) !== count($row)) {
                    // If the counts don't match, ignore this row
                    return null;
                }
                return array_combine($header, $row);
            }, array_slice($data, 1)));
            

            // Validate and process each row (Skipping headers)
            foreach ($mappedData as $row) {
                if (empty($row['Product Name']) || empty($row['Price ($)'])) {
                    continue;
                }

                // Insert or update project logic
                Product::updateOrCreate(
                    ['product_name' => $row['Product Name']],
                    [
                        'product_name' => $row['Product Name'],
                        'unit_price' => floatval(str_replace(",","",$row['Price ($)'])),
                    ]
                );
            }

            // // Validate and process each row (Skipping headers)
            // foreach ($mappedData as $row) {
            //     if (empty($row['ItemName']) || empty($row['SalesUnitPrice'])) {
            //         continue;
            //     }

            //     // Insert or update project logic
            //     Product::updateOrCreate(
            //         ['product_name' => $row['ItemName']],
            //         [
            //             'product_name' => $row['ItemName'],
            //             'unit_price' => floatval(str_replace(",","",$row['SalesUnitPrice'])),
            //         ]
            //     );
            // }

            return redirect()->route('products.index')->with('success', 'Products imported successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['import_file' => $e->getMessage()]);
        }
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
