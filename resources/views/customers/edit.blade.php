@extends('layouts.master')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-semibold text-gray-700 mb-6">Edit Customer</h1>

    <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf
        @method('PUT')

        <!-- Customer Info Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                <input type="email" name="email" id="email" value="{{ old('email', $customer->email) }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="first_name" class="block text-gray-700 text-sm font-bold mb-2">First Name:</label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $customer->first_name) }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('first_name') border-red-500 @enderror">
                @error('first_name')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="last_name" class="block text-gray-700 text-sm font-bold mb-2">Last Name:</label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $customer->last_name) }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('last_name') border-red-500 @enderror">
                @error('last_name')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Postal Address Section -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Postal Address</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="po_address_line1" class="block text-gray-700 text-sm font-bold mb-2">Address Line 1:</label>
                    <input type="text" name="po_address_line1" id="po_address_line1" value="{{ old('po_address_line1', $customer->po_address_line1) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('po_address_line1') border-red-500 @enderror">
                    @error('po_address_line1')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="po_address_line2" class="block text-gray-700 text-sm font-bold mb-2">Address Line 2:</label>
                    <input type="text" name="po_address_line2" id="po_address_line2" value="{{ old('po_address_line2', $customer->po_address_line2) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('po_address_line2') border-red-500 @enderror">
                    @error('po_address_line2')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="po_address_line3" class="block text-gray-700 text-sm font-bold mb-2">Address Line 3:</label>
                    <input type="text" name="po_address_line3" id="po_address_line3" value="{{ old('po_address_line3', $customer->po_address_line3) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('po_address_line3') border-red-500 @enderror">
                    @error('po_address_line3')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="po_address_line4" class="block text-gray-700 text-sm font-bold mb-2">Address Line 4:</label>
                    <input type="text" name="po_address_line4" id="po_address_line4" value="{{ old('po_address_line4', $customer->po_address_line4) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('po_address_line4') border-red-500 @enderror">
                    @error('po_address_line4')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="po_city" class="block text-gray-700 text-sm font-bold mb-2">City:</label>
                    <input type="text" name="po_city" id="po_city" value="{{ old('po_city', $customer->po_city) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('po_city') border-red-500 @enderror">
                    @error('po_city')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="po_region" class="block text-gray-700 text-sm font-bold mb-2">Region:</label>
                    <input type="text" name="po_region" id="po_region" value="{{ old('po_region', $customer->po_region) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('po_region') border-red-500 @enderror">
                    @error('po_region')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="po_zip_code" class="block text-gray-700 text-sm font-bold mb-2">Zip Code:</label>
                    <input type="text" name="po_zip_code" id="po_zip_code" value="{{ old('po_zip_code', $customer->po_zip_code) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('po_zip_code') border-red-500 @enderror">
                    @error('po_zip_code')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="po_country" class="block text-gray-700 text-sm font-bold mb-2">Country:</label>
                    <input type="text" name="po_country" id="po_country" value="{{ old('po_country', $customer->po_country) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('po_country') border-red-500 @enderror">
                    @error('po_country')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Shipping Address Section (SA) -->
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Shipping Address</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="sa_address_line1" class="block text-gray-700 text-sm font-bold mb-2">Address Line 1:</label>
                    <input type="text" name="sa_address_line1" id="sa_address_line1" value="{{ old('sa_address_line1', $customer->sa_address_line1) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sa_address_line1') border-red-500 @enderror">
                    @error('sa_address_line1')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="sa_address_line2" class="block text-gray-700 text-sm font-bold mb-2">Address Line 2:</label>
                    <input type="text" name="sa_address_line2" id="sa_address_line2" value="{{ old('sa_address_line2', $customer->sa_address_line2) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sa_address_line2') border-red-500 @enderror">
                    @error('sa_address_line2')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="sa_address_line3" class="block text-gray-700 text-sm font-bold mb-2">Address Line 3:</label>
                    <input type="text" name="sa_address_line3" id="sa_address_line3" value="{{ old('sa_address_line3', $customer->sa_address_line3) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sa_address_line3') border-red-500 @enderror">
                    @error('sa_address_line3')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="sa_address_line4" class="block text-gray-700 text-sm font-bold mb-2">Address Line 4:</label>
                    <input type="text" name="sa_address_line4" id="sa_address_line4" value="{{ old('sa_address_line4', $customer->sa_address_line4) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sa_address_line4') border-red-500 @enderror">
                    @error('sa_address_line4')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="sa_city" class="block text-gray-700 text-sm font-bold mb-2">City:</label>
                    <input type="text" name="sa_city" id="sa_city" value="{{ old('sa_city', $customer->sa_city) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sa_city') border-red-500 @enderror">
                    @error('sa_city')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="sa_region" class="block text-gray-700 text-sm font-bold mb-2">Region:</label>
                    <input type="text" name="sa_region" id="sa_region" value="{{ old('sa_region', $customer->sa_region) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sa_region') border-red-500 @enderror">
                    @error('sa_region')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="sa_zip_code" class="block text-gray-700 text-sm font-bold mb-2">Zip Code:</label>
                    <input type="text" name="sa_zip_code" id="sa_zip_code" value="{{ old('sa_zip_code', $customer->sa_zip_code) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sa_zip_code') border-red-500 @enderror">
                    @error('sa_zip_code')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="sa_country" class="block text-gray-700 text-sm font-bold mb-2">Country:</label>
                    <input type="text" name="sa_country" id="sa_country" value="{{ old('sa_country', $customer->sa_country) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sa_country') border-red-500 @enderror">
                    @error('sa_country')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

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