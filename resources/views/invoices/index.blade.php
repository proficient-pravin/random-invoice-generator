@extends('layouts.master')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <!-- Title on the left -->
        <h1 class="text-2xl font-semibold text-gray-700 mb-4 md:mb-0">Invoices</h1>

        <!-- Back Button on the right -->
        <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
            <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back
            </a>
        </div>
    </div>

    <!-- Invoice Table -->
    <div class="overflow-x-auto relative shadow-md sm:rounded-lg bg-white rounded-lg">
        <table id="invoicesTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left" style="text-align: left !important;">Invoice Number</th>  <!-- Align Invoice Number to the left -->
                    <th scope="col" class="px-6 py-3 text-left">Date</th>        <!-- Align Customer to the left -->
                    <th scope="col" class="px-6 py-3 text-left">Customer</th>        <!-- Align Customer to the left -->
                    <th scope="col" class="px-6 py-3 text-left">Total</th>          <!-- Align Total to the left -->
                    <th scope="col" class="px-6 py-3 text-left">Items</th>         <!-- Align Actions to the left -->
                    <th scope="col" class="px-6 py-3 text-left">Action</th>         <!-- Align Actions to the left -->
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.0.3"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.2.1/css/dataTables.dataTables.min.css">

<!-- DataTables JS -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/2.2.1/js/dataTables.min.js"></script>

<!-- Font Awesome for icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<script>
    $(document).ready(function () {
        var table = $('#invoicesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('invoices.index') }}",  // Adjust route if necessary
            columns: [
                { data: 'invoice_number', name: 'invoice_number', className: 'text-left' },
                { data: 'invoice_date', name: 'invoice_date', className: 'text-left' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'total', name: 'total' },
                { data: null, name: 'items', defaultContent: '' } ,// Empty column for actions (icons)
                { data: 'actions', name: 'actions' } // Empty column for actions (icons)
            ],
            lengthChange: false,  // Hides the "per page" button
            responsive: true,  // Makes the table responsive
            rowCallback: function (row, data) {
                var invoiceItems = data.items;
                $(row).find('td:eq(0)').removeClass('dt-type-numeric');
                // Add an icon for expand/collapse
                $(row).find('td:eq(4)').html('<i class="fas fa-plus-circle"></i>');

                $(row).on('click', function () {
                    var icon = $(this).find('td:eq(3) i');
                    var expanded = $(this).hasClass('shown');

                    if (expanded) {
                        $(this).removeClass('shown');
                        $(this).next('tr').remove();
                        icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                    } else {
                        $(this).addClass('shown');
                        
                        var itemTable = `
                            <tr class="invoice-items-row">
                                <td colspan="4">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                                <tr>
                                                    <th class="px-6 py-3 text-left">Item Name</th>     <!-- Align left -->
                                                    <th class="px-6 py-3 text-left">Quantity</th>      <!-- Align left -->
                                                    <th class="px-6 py-3 text-left">Unit Price</th>    <!-- Align left -->
                                                    <th class="px-6 py-3 text-left">Tax</th>           <!-- Align left -->
                                                    <th class="px-6 py-3 text-left">Amount</th>        <!-- Align left -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${invoiceItems.map(item => `
                                                    <tr>
                                                        <td class="px-6 py-3 text-left">${item.name}</td>  <!-- Align left -->
                                                        <td class="px-6 py-3 text-left">${item.quantity}</td>  <!-- Align left -->
                                                        <td class="px-6 py-3 text-left">${item.unit_price}</td>  <!-- Align left -->
                                                        <td class="px-6 py-3 text-left">${item.tax} (${item.tax_percentage}%)</td>  <!-- Align left -->
                                                        <td class="px-6 py-3 text-left">${item.amount}</td>  <!-- Align left -->
                                                    </tr>
                                                `).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>`;
                        
                        $(this).after(itemTable);
                        icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                    }
                });
            }
        });
    });
</script>
@endsection
