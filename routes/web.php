<?php

use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Customer\TagController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Invoice\InvoiceController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

use App\Http\Controllers\InvoiceGenerationController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\ProfileController;
use App\Models\Invoice;

Route::get('/generate-invoices', [InvoiceGenerationController::class, 'showForm'])->middleware(['auth', 'verified'])->name('invoices.generate');
Route::post('/generate-invoices', [InvoiceGenerationController::class, 'generateInvoices'])->middleware(['auth', 'verified'])->name('generate.invoices');

Route::post('/update-csv', [InvoiceGenerationController::class, 'updateCsvFiles'])->name('update.csv');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// PROD
Route::middleware('auth')->prefix('customers')->name('customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/create', [CustomerController::class, 'create'])->name('create');
    Route::post('/store', [CustomerController::class, 'store'])->name('store');
    // Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
    Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
    Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    Route::post('/import', [CustomerController::class, 'import'])->name('import');
    Route::get('/view/{customer}', [CustomerController::class, 'view'])->name('view');
    // Route::get('/view', function(){
    //     return view('customers.view');
    // })->name('view');
    Route::post('/update-inline', [CustomerController::class, 'updateInline'])->name('updateInline');
});
Route::middleware('auth')->prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/create', [ProductController::class, 'create'])->name('create');
    Route::post('/store', [ProductController::class, 'store'])->name('store');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    Route::post('/import', [ProductController::class, 'import'])->name('import');
});
Route::middleware('auth')->prefix('tags')->name('tags.')->group(function () {
    Route::get('/', [TagController::class, 'index'])->name('index');
    Route::get('/create', [TagController::class, 'create'])->name('create');
    Route::post('/store', [TagController::class, 'store'])->name('store');
    Route::get('/{tag}/edit', [TagController::class, 'edit'])->name('edit');
    Route::put('/{tag}', [TagController::class, 'update'])->name('update');
    Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth')->prefix('invoices')->name('invoices.')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/{invoice}/download', [InvoiceController::class, 'download'])->name('download');
    Route::get('/{invoice}/preview', [InvoiceController::class, 'preview'])->name('preview');
});

require __DIR__.'/auth.php';
