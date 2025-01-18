@extends('layouts.master')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <!-- Title on the left -->
        <h1 class="text-2xl font-semibold text-gray-700 mb-4 md:mb-0">Tags</h1>
    
        <!-- Add Tag Button and Back Button on the right -->
        <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
            <a href="{{ route('tags.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add Tag
            </a>
            <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back
            </a>
        </div>
    </div>    

    <!-- Tag Table -->
    <div class="overflow-x-auto relative shadow-md sm:rounded-lg bg-white rounded-lg">
        <table id="tagsTable" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Tag Name</th>
                    <th scope="col" class="px-6 py-3">Tag Color</th>
                    <th scope="col" class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be loaded via Yajra DataTables -->
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
<script>
    $(document).ready(function () {
        $('#tagsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tags.index') }}",
            columns: [
                { data: 'name', name: 'name' },
                { data: 'bg_color', name: 'bg_color' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            lengthChange: false,  // Hides the "per page" button
            responsive: true,  // Makes the table responsive
        });
    });
</script>
@endsection
