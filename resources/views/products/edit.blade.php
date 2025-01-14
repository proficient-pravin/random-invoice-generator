@extends('layouts.master')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-700 mb-4 md:mb-0">Edit Product</h1>
        <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
            <a href="{{ route('products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back
            </a>
        </div>
    </div>
    <form action="{{ route('products.update', $product->id) }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf
        @method('PUT')

        <!-- Product Info Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="product_name" class="block text-gray-700 text-sm font-bold mb-2">Product Name:</label>
                <input type="text" name="product_name" id="product_name" value="{{ old('product_name', $product->product_name) }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('product_name') border-red-500 @enderror">
                @error('product_name')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="unit_price" class="block text-gray-700 text-sm font-bold mb-2">Unit Price:</label>
                <input type="text" name="unit_price" id="unit_price" value="{{ old('unit_price', $product->unit_price) }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('unit_price') border-red-500 @enderror">
                @error('unit_price')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Additional fields for product can be added here -->

        <!-- Submit Button -->
        <div class="flex items-center justify-between mt-6">
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Update
            </button>
        </div>
    </form>
</div>
@endsection
