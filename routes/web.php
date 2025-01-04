<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

use App\Http\Controllers\InvoiceGenerationController;

Route::get('/', [InvoiceGenerationController::class, 'showForm']);
Route::post('generate-invoices', [InvoiceGenerationController::class, 'generateInvoices'])->name('generate.invoices');