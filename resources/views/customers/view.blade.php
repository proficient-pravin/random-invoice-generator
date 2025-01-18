@extends('layouts.master')

@section('content')
    <div class="container mx-auto p-6">
      

<ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400">
    <li class="me-2">
        <a href="#" aria-current="page" class="inline-block p-4 text-blue-600 bg-gray-100 rounded-t-lg active dark:bg-gray-800 dark:text-blue-500">Profile</a>
    </li>
    <li class="me-2">
        <a href="#" class="inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300">Invoices</a>
    </li>
</ul>

     
    </div>
@endsection
