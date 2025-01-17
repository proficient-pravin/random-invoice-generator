@extends('layouts.master')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <!-- Title on the left -->
        <h1 class="text-2xl font-semibold text-gray-700 mb-4 md:mb-0">Customers</h1>
    
        <!-- Buttons on the right -->
        <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
            <button id="importCustomerButton" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Import Customers
            </button>
            <a href="{{ route('customers.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add Customer
            </a>
            <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back
            </a>
        </div>
    </div>

    <!-- Customer Table -->
    <div class="overflow-x-auto relative shadow-md sm:rounded-lg bg-white rounded-lg">
        <table id="customersTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">ID</th>
                    <th scope="col" class="px-6 py-3">Email</th>
                    <th scope="col" class="px-6 py-3">First Name</th>
                    <th scope="col" class="px-6 py-3">Last Name</th>
                    <th scope="col" class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be loaded via Yajra DataTables -->
            </tbody>
        </table>
    </div>
</div>

<!-- Import Customer Modal -->
<div id="importCustomerModal" class="fixed inset-0 z-50 hidden bg-gray-800 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-700">Import Customers</h2>
            <button id="closeImportModal" class="text-gray-500 hover:text-gray-700 float-right">×</button>
        </div>
        <div class="p-6">
            <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="importFile" class="block text-sm font-medium text-gray-700">Select CSV File</label>
                    <input type="file" name="import_file" id="importFile" accept=".csv" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('import_file')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Submit
                    </button>
                    <button type="button" id="cancelImportButton" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css">

<!-- DataTables JS -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#customersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('customers.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'email', name: 'email' },
                { data: 'first_name', name: 'first_name' },
                { data: 'last_name', name: 'last_name' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            lengthChange: false,
            responsive: true,
        });

        // Show import modal
        $('#importCustomerButton').click(function () {
            $('#importCustomerModal').removeClass('hidden');
        });

        // Hide import modal
        $('#closeImportModal, #cancelImportButton').click(function () {
            $('#importCustomerModal').addClass('hidden');
        });
    });
</script>
@endsection
