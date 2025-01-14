<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
     /**
     * Display a listing of the invoices.
     */
    public function index()
    {
        if (request()->ajax()) {
            $invoices = Invoice::with('customer', 'items') // Eager load customer and items data
                ->select('invoices.id', 'invoices.invoice_number', 'invoices.invoice_date', 'invoices.customer_id') // Select the necessary columns
                ->addSelect(\DB::raw('ROUND(SUM(invoice_items.amount), 2) as total')) // Calculate the total dynamically from invoice items
                ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id') // Join invoice_items table to get the amounts
                ->groupBy('invoices.id')
                ->get();
        
            return DataTables::of($invoices)
                ->addColumn('customer_name', function ($invoice) {
                    return $invoice->customer->first_name . " " . $invoice->customer->last_name; // Return the full name of the customer
                })
                ->addColumn('invoice_items', function ($invoice) {
                    return $invoice->items->map(function ($item) {
                        return [
                            'name' => $item->name,
                            'description' => $item->description,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'tax' => $item->tax,
                            'tax_percentage' => $item->tax_percentage,
                            'amount' => $item->amount,
                        ];
                    });
                })
                ->make(true);
        }
        
    
        return view('invoices.index');
    }

}
