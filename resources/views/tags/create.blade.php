@extends('layouts.master')

@section('content')
    <div class="container mx-auto p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-700 mb-4 md:mb-0">Add Tag</h1>
            <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
                <a href="{{ route('tags.index') }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back
                </a>
            </div>
        </div>

        <form action="{{ route('tags.store') }}" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf

            <!-- Tag Info Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Tag Name:</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="bg_color" class="block text-gray-700 text-sm font-semibold mb-2">Tag Color:</label>

                    <div class="relative">
                        <!-- Custom Color Picker Input (20x20px square) -->
                        <input type="color" name="bg_color" id="bg_color" value="{{ old('bg_color') }}"
                            class="block w-10 h-10 p-0 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 hover:bg-gray-100 transition ease-in-out duration-300 @error('bg_color') border-red-500 @enderror"
                            title="Choose a color">

                        <!-- Tooltip Icon for Color Picker -->
                        <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500">
                            <i class="fas fa-palette"></i>
                        </span>
                    </div>

                    <!-- Error message -->
                    @error('bg_color')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>



            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between mt-6">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Submit
                </button>
            </div>
        </form>
    </div>
@endsection
