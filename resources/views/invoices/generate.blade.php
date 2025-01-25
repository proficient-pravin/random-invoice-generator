@extends('layouts.master')

@section('content')
<div class="container">
    <!-- Invoice Generation Section -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-700 mb-4 md:mb-0">Generate Invoices</h1>
    </div>
    <div class="p-6">
        <form method="POST" action="{{ route('generate.invoices') }}" id="invoiceForm">
            @csrf
            <input type="text" value="{{ $_GET['debug'] ?? 0 }}" class="hidden mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="debug">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="start_date" required>
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="end_date" required>
                </div>

                <!-- Start Invoice Number -->
                <div>
                    <label for="start_invoice_number" class="block text-sm font-medium text-gray-700">Start Invoice Number</label>
                    <input type="number" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="start_invoice_number" value="{{ $startInvoiceNumber }}" required>
                </div>

                <!-- Number of Invoices -->
                <div>
                    <label for="num_invoices" class="block text-sm font-medium text-gray-700">Number of Invoices</label>
                    <input type="number" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="num_invoices">
                </div>

                  <!-- Number of Client -->
                <div>
                    <label for="num_client" class="block text-sm font-medium text-gray-700">Number of Client</label>
                    <input type="number" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="num_client">
                </div>

                <!-- Tax Percentage -->
                <div class="">
                    <label for="tax_percentage" class="block text-sm font-medium text-gray-700">Tax Percentage</label>
                    <input type="number" value="8.38" step="0.01" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="tax_percentage" required>
                </div>

                <!-- Total Amount -->
                <div>
                    <label for="total_amount" class="block text-sm font-medium text-gray-700">Total Amount</label>
                    <input type="number" step="0.01" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="total_amount" required>
                </div>
                <div>
                    <label for="print_address_line1" class="block text-sm font-medium text-gray-700">Address Line 1</label>
                    <input type="text" value="5525 S Decatur Blvd # 106" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="print_address_line1" required>
                </div>
                <div>
                    <label for="print_address_line2" class="block text-sm font-medium text-gray-700">Address Line 2</label>
                    <input type="text" value="Las Vegas 89118 USA" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="print_address_line2" required>
                </div>
                <div>
                    <label for="print_address_line3" class="block text-sm font-medium text-gray-700">Address Line 3</label>
                    <input type="text" value="" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="print_address_line3">
                </div>
                <div>
                    <label for="print_address_line4" class="block text-sm font-medium text-gray-700">Address Line 4</label>
                    <input type="text" value="" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="print_address_line4">
                </div>
            </div>

            <div class="text-center mt-6">
                <!-- Submit Button -->
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 focus:outline-none">Generate Invoices</button>
            </div>
        </form>

        <!-- Loader -->
        <div id="loader" class="text-center mt-4 hidden">
            <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent border-solid rounded-full animate-spin"></div>
        </div>
    </div>

    {{-- <!-- CSV Update Section -->
    <div class="bg-white shadow rounded mt-8">
        <div class="bg-gray-800 text-white text-center py-4 rounded-t">
            <h4 class="text-lg font-semibold">Update Product and Customer CSV</h4>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('update.csv') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Upload Product CSV -->
                    <div>
                        <label for="product_csv" class="block text-sm font-medium text-gray-700">Upload Product CSV</label>
                        <input type="file" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="product_csv" accept=".csv">
                    </div>

                    <!-- Upload Customer CSV -->
                    <div>
                        <label for="customer_csv" class="block text-sm font-medium text-gray-700">Upload Customer CSV</label>
                        <input type="file" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" name="customer_csv" accept=".csv">
                    </div>
                </div>

                <div class="text-center mt-6">
                    <!-- Submit Button -->
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700 focus:outline-none">Update CSV Files</button>
                </div>
            </form>
        </div>
    </div> --}}
</div>
@endsection
