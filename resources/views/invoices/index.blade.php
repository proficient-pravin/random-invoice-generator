@extends('layouts.master')

@section('content')
    <div class="container mx-auto p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <!-- Title on the left -->
            <h1 class="text-2xl font-semibold text-gray-700 mb-4 md:mb-0">Invoices</h1>

            <!-- Back Button on the right -->
            <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
                <a href="{{ route('dashboard') }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col md:flex-row justify-left items-center mb-4">

            <!-- Date Range Filter -->
            <div class="mb-4 md:mb-0">
                <label for="daterange" class="block text-sm font-medium text-gray-700">Filter by Date Range</label>
                <input type="text" id="daterange" class="border-gray-300 rounded-lg w-full md:w-64">
            </div>

            <!-- Customer Multi-Select -->
            <div class="mb-4 md:mb-0  ml-5">
                <label for="customerFilter" class="block text-sm font-medium text-gray-700">Filter by Customer</label>
                <select id="customerFilter" class="w-full md:w-64 border-gray-300 rounded-lg select2-init" multiple="multiple">
                    @foreach ($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}">{{ "$customer->first_name $customer->last_name" }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Invoice Table -->
        <div class="overflow-x-auto relative shadow-md sm:rounded-lg bg-white rounded-lg">
            <table id="invoicesTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left">Invoice Number</th>
                        <th scope="col" class="px-6 py-3 text-left">Date</th>
                        <th scope="col" class="px-6 py-3 text-left">Customer</th>
                        <th scope="col" class="px-6 py-3 text-left">Total</th>
                        <th scope="col" class="px-6 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <style>
        #dt-length-0 {
            width: 150%;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css">

    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>

    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">


    <!-- Include DateRange Picker CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        $(document).ready(function() {
            // Initialize Date Range Picker
            $('#daterange').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                },
                startDate: moment().startOf('month'), // Default start date
                endDate: moment().endOf('month'), // Default end date
                showDropdowns: true, // Enables dropdowns for year and month selection
                autoUpdateInput: true
            }, function(start, end, label) {
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end
                    .format('YYYY-MM-DD'));
            });

            // Initialize DataTable
            var table = $('#invoicesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('invoices.index') }}",
                    data: function(d) {
                        d.customer = $('#customerFilter').val();
                        d.start_date = $('#daterange').data('daterangepicker').startDate.format(
                            'YYYY-MM-DD');
                        d.end_date = $('#daterange').data('daterangepicker').endDate.format(
                            'YYYY-MM-DD');
                    }
                },
                columns: [{
                        data: 'invoice_number',
                        name: 'invoice_number',
                        className: 'text-left'
                    },
                    {
                        data: 'invoice_date',
                        name: 'invoice_date',
                        className: 'text-left'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'actions',
                        name: 'actions'
                    }
                ],
                lengthChange: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, 100, 1000],
                    [10, 25, 50, 100, "1000"]
                ],
                pageLength: 50,
                language: {
                    lengthMenu: "_MENU_" // Only the dropdown without the label
                },
                // dom: 'lfrtip', // Includes the length menu dropdown        
            });

             // Initialize Select2 for customer filter
            $('#customerFilter').select2({
                allowClear: true, // Allow users to clear selections
                tags: true,       // Enable tagging
                // width: '150%'     // Adjust width to match the parent element
            });
            
            // Apply filters
            $('#customerFilter, #daterange').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
