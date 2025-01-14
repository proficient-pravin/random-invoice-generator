<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

use App\Http\Controllers\InvoiceGenerationController;
use App\Http\Controllers\ProfileController;

// Route::get('/generate-invoices', [InvoiceGenerationController::class, 'showForm']);
Route::post('/generate-invoices', [InvoiceGenerationController::class, 'generateInvoices'])->name('generate.invoices');
Route::post('/update-csv', [InvoiceGenerationController::class, 'updateCsvFiles'])->name('update.csv');

Route::get('/dashboard', [InvoiceGenerationController::class, 'showForm'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/', [InvoiceGenerationController::class, 'showForm'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
