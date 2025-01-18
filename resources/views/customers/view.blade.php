@extends('layouts.master')

@section('content')
    <div class="container mx-auto">
        <button data-modal-target="invoicePreviewModal" data-modal-toggle="invoicePreviewModal" id="openInvoicePreviewModal"
            class="hidden block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
            type="button">
            Toggle modal
        </button>
        <!-- Main modal -->
        <div id="invoicePreviewModal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative p-4 w-full max-w-4xl max-h-full"> <!-- Increased width to max-w-4xl -->
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Invoice Preview
                        </h3>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="invoicePreviewModal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-4 md:p-5 space-y-4">
                        <!-- iframe to stream content -->
                        <iframe src="" width="100%" height="500px" frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <main class="flex-1">
            <!-- Top Bar -->
            <header class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-semibold">{{ "$customer->first_name $customer->last_name" }}</h1>
                    <span class="text-xs px-2 py-1 rounded text-white"
                        style="background-color: {{ $customer->tag->bg_color }}">
                        {{ $customer->tag->name }}
                    </span>
                </div>
                <h1 class="text-xl font-semibold">Total Invoice
                    ${{ number_format($customer->total_invoice_amount, 2, '.', ',') }}</h1>
                <p class="text-sm text-gray-600">{{ $customer->email }}</p>
            </header>

            <!-- Address and Details -->
            <section class="bg-white p-4 rounded-md shadow-sm">
                <h2 class="text-base font-bold mb-3">Customer Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Basic Details Card -->
                    <div class="p-3 border rounded-md bg-gray-50 shadow-sm">
                        <h3 class="text-sm font-semibold text-purple-600">Basic Details</h3>
                        <ul class="text-sm text-gray-600 mt-1">
                            <li><strong>First Name:</strong> {{ $customer->first_name }}</li>
                            <li><strong>Last Name:</strong> {{ $customer->last_name }}</li>
                            <li><strong>Email:</strong> {{ $customer->email }}</li>
                        </ul>
                    </div>

                    <!-- Postal Address Card -->
                    <div class="p-3 border rounded-md bg-gray-50 shadow-sm">
                        <h3 class="text-sm font-semibold text-blue-600">Postal Address</h3>
                        <ul class="text-sm text-gray-600 mt-1">
                            <li><strong>Line 1:</strong> {{ $customer->po_address_line1 }}</li>
                            <li><strong>City:</strong> {{ $customer->po_city }}</li>
                            <li><strong>ZIP:</strong> {{ $customer->po_zip_code }}</li>
                            <li><strong>Country:</strong> {{ $customer->po_country }}</li>
                        </ul>
                    </div>

                    <!-- Shipping Address Card -->
                    <div class="p-3 border rounded-md bg-gray-50 shadow-sm">
                        <h3 class="text-sm font-semibold text-green-600">Shipping Address</h3>
                        <ul class="text-sm text-gray-600 mt-1">
                            <li><strong>Line 1:</strong> {{ $customer->sa_address_line1 }}</li>
                            <li><strong>City:</strong> {{ $customer->sa_city }}</li>
                            <li><strong>ZIP:</strong> {{ $customer->sa_zip_code }}</li>
                            <li><strong>Country:</strong> {{ $customer->sa_country }}</li>
                        </ul>
                    </div>
                </div>
            </section>
        </main>



        <div class="pt-6">
            <ul
                class="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400">
                <li class="me-2 tab-items">
                    <a href="#" aria-current="page" data-item="statistics"
                        class="inline-block p-4 text-blue-600 bg-gray-100 rounded-t-lg active dark:bg-gray-800 dark:text-blue-500">Statistics</a>
                </li>
                <li class="me-2 tab-items">
                    <a href="#" data-item="invoices"
                        class="inline-block p-4 hover:text-gray-600 rounded-t-lg dark:hover:bg-gray-800 dark:hover:text-gray-300">Invoices</a>
                </li>
            </ul>
        </div>
        <div class="statistics-tab">
            <h1 class="text-xl font-semibold mt-5">Daily Chart Data</h1>
            <div><canvas id="dailyChart"></canvas></div>
            <h1 class="text-xl font-semibold mt-5">Monthly Chart Data</h1>
            <div><canvas id="yearlyChart"></canvas></div>

        </div>
        <div class="invoice-tab hidden">
            <!-- Filters -->
            <div class="flex flex-col md:flex-row justify-left items-center mb-4">
                <!-- Date Range Filter -->
                <div class="mb-4 md:mb-0">
                    <label for="daterange" class="block text-sm font-medium text-gray-700">Filter by Date Range</label>
                    <input type="text" id="daterange" class="border-gray-300 rounded-lg w-full md:w-64">
                </div>
            </div>

            <!-- Invoice Table -->
            <div class="overflow-x-auto relative shadow-md sm:rounded-lg bg-white rounded-lg">
                <table id="invoicesTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left">Invoice Number</th>
                            <th scope="col" class="px-6 py-3 text-left">Date</th>
                            <th scope="col" class="px-6 py-3 text-left">Total</th>
                            <th scope="col" class="px-6 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            $('#daterange').val('');
            // Initialize DataTable
            var table = $('#invoicesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('invoices.index') }}",
                    data: function(d) {
                        d.customer = ["{{ $customer->id }}"];
                        var daterange = $('#daterange').val();
                        if (daterange) {
                            var dates = daterange.split(' to ');
                            d.start_date = dates[0];
                            d.end_date = dates[1];
                        }
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
                pageLength: 10,
                language: {
                    lengthMenu: "_MENU_" // Only the dropdown without the label
                },
                drawCallback: function(settings) {
                    $('body').find('.openOnvoicePreviewModal').on('click', function() {
                        $('iframe').attr('src', $(this).data('url'));
                        $('#openInvoicePreviewModal').click()
                    });
                }
            });

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
                alwaysShowCalendars: true,
                startDate: moment().startOf('month'), // Default start date
                endDate: moment().endOf('month'), // Default end date
                showDropdowns: true, // Enables dropdowns for year and month selection
                autoUpdateInput: false
            }, function(start, end, label) {
                // Manually update the input value when a range is selected
                $('#daterange').val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                table.ajax.reload();
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end
                    .format('YYYY-MM-DD'));
            });

            // Initialize Select2 for customer filter
            $('#customerFilter').select2({
                allowClear: true, // Allow users to clear selections
                tags: true, // Enable tagging
                // width: '150%'     // Adjust width to match the parent element
            });

            // Apply filters
            $('#customerFilter, #daterange').on('change', function() {
                table.ajax.reload();
            });

            $('.tab-items a').on("click", function(e) {
                e.preventDefault();
                var activeClass =
                    'inline-block p-4 text-blue-600 bg-gray-100 rounded-t-lg active dark:bg-gray-800 dark:text-blue-500';
                var inActiveClass =
                    'inline-block p-4 hover:text-gray-600 rounded-t-lg dark:hover:bg-gray-800 dark:hover:text-gray-300';
                $(this).parent().siblings().find('a').attr('class', inActiveClass)
                $(this).attr('class', activeClass)
                if ($(this).data('item') == 'invoices') {
                    $('.invoice-tab').removeClass('hidden')
                } else {
                    $('.invoice-tab').addClass('hidden')
                }
                if ($(this).data('item') == 'statistics') {
                    $('.statistics-tab').removeClass('hidden')
                } else {
                    $('.statistics-tab').addClass('hidden')
                }
            })
        });

        (async function() {
            const data = [{
                    year: 2010,
                    count: 10
                },
                {
                    year: 2011,
                    count: 20
                },
                {
                    year: 2012,
                    count: 15
                },
                {
                    year: 2013,
                    count: 25
                },
                {
                    year: 2014,
                    count: 22
                },
                {
                    year: 2015,
                    count: 30
                },
                {
                    year: 2016,
                    count: 28
                },
            ];

            var yearlyChartData = {!! json_encode($chartData) !!};
            var dailyChartData = {!! json_encode($dailyChartData) !!};

            new Chart(
                document.getElementById('dailyChart'), {
                    type: 'line',
                    data: {
                        labels: dailyChartData.labels,
                        datasets: [{
                            label: 'Invoices last 30 months',
                            data: dailyChartData.data
                        }]
                    }
                }
            );

            new Chart(document.getElementById('yearlyChart'), {
                type: 'line',
                data: {
                    labels: yearlyChartData.labels,
                    datasets: [{
                        label: 'Invoices last 12 months',
                        data: yearlyChartData.data,
                        borderColor: '#4e73df', // Line color
                        backgroundColor: 'rgba(78, 115, 223, 0.1)', // Fill color
                        fill: true, // Fill under the line
                        tension: 0.4 // Smooth curve
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                    family: 'Arial'
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false, // Remove grid for x-axis
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false, // Remove grid for y-axis
                            },
                            ticks: {
                                font: {
                                    size: 12
                                },
                                beginAtZero: true
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 4, // Adjust point size
                            backgroundColor: '#4e73df', // Point color
                            borderColor: '#4e73df', // Border color of points
                            hoverRadius: 6 // Hover effect size
                        }
                    }
                }
            });
        })();
    </script>
@endsection
