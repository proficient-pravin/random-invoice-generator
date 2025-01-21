<?php
namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
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
            $invoices = Invoice::with('customer', 'items')
                ->when(! empty(request()->customer), function ($q) {
                    $q->whereHas('customer', function ($q) {
                        $q->whereIn('id', request()->customer);
                    });
                })
                ->when(request()->start_date && request()->end_date, function ($q) {
                    $q->whereBetween('invoice_date', [request()->start_date, request()->end_date]);
                })
                ->select('invoices.id', 'invoices.invoice_number', 'invoices.invoice_date', 'invoices.customer_id') // Select the necessary columns
                ->addSelect(\DB::raw('ROUND(SUM(invoice_items.amount + invoice_items.tax), 2) as total'))           // Calculate the total dynamically from invoice items
                ->addSelect(\DB::raw('ROUND(SUM(invoice_items.amount), 2) as sub_total'))           // Calculate the total dynamically from invoice items
                ->addSelect(\DB::raw('ROUND(SUM(invoice_items.tax), 2) as tax'))           // Calculate the total dynamically from invoice items
                ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')                             // Join invoice_items table to get the amounts
                ->groupBy('invoices.id')
                ->get();

            return DataTables::of($invoices)
                ->addColumn('customer_name', function ($invoice) {
                    return $invoice->customer->first_name . " " . $invoice->customer->last_name; // Return the full name of the customer
                })
                ->addColumn('invoice_items', function ($invoice) {
                    return $invoice->items->map(function ($item) {
                        return [
                            'name'           => $item->name,
                            'description'    => $item->description,
                            'quantity'       => $item->quantity,
                            'unit_price'     => $item->unit_price,
                            'tax'            => $item->tax,
                            'tax_percentage' => $item->tax_percentage,
                            'amount'         => $item->amount,
                        ];
                    });
                })
                ->addColumn('actions', function ($customer) {
                    return view('invoices.actions', compact('customer'));
                })
                ->make(true);
        }

        return view('invoices.index', [
            'customers' => Customer::all(),
        ]);
    }

    /**
     * Show the form for download the specified invoice.
     *
     * @param \App\Models\Invoice $invoice
     */
    public function download(Invoice $invoice)
    {
        $invoice = $invoice->load('items', 'customer')->toArray();

        $subtotal = array_sum(array_column($invoice['items'], 'amount'));
        $totalTax = array_sum(array_column($invoice['items'], 'tax'));

        $transformed = array_merge($invoice['customer'], [
            "invoice_number" => $invoice['invoice_number'],
            "customer_id"    => $invoice['customer_id'],
            "invoice_date"   => $invoice['invoice_date'],
            "created_at"     => $invoice['created_at'],
            "updated_at"     => $invoice['updated_at'],
            "invoice_items"  => $invoice['items'], // Renaming items to invoice_items
            'subtotal'       => number_format($subtotal, 2, '.', ','),
            'total_tax'      => number_format($totalTax, 2, '.', ','),
            'total'          => number_format(($subtotal + $totalTax), 2, '.', ','),
        ]);

        // Generate PDF for the invoice
        $pdf     = PDF::loadView('invoice_template_final', ['invoice' => $transformed]);
        $pdfPath = 'pdf_invoice_' . ($transformed['invoice_number']) . '.pdf';

        return $pdf->download($pdfPath);

    }

    /**
     * Show the form for preview the specified invoice.
     *
     * @param \App\Models\Invoice $invoice
     */
    public function preview(Invoice $invoice)
    {
        $invoice = $invoice->load('items', 'customer')->toArray();

        $subtotal = array_sum(array_column($invoice['items'], 'amount'));
        $totalTax = array_sum(array_column($invoice['items'], 'tax'));

        $transformed = array_merge($invoice['customer'], [
            "invoice_number" => $invoice['invoice_number'],
            "customer_id"    => $invoice['customer_id'],
            "invoice_date"   => $invoice['invoice_date'],
            "created_at"     => $invoice['created_at'],
            "updated_at"     => $invoice['updated_at'],
            "invoice_items"  => $invoice['items'], // Renaming items to invoice_items
            'subtotal'       => number_format($subtotal, 2, '.', ','),
            'total_tax'      => number_format($totalTax, 2, '.', ','),
            'total'          => number_format(($subtotal + $totalTax), 2, '.', ','),
        ]);

        // Generate PDF for the invoice
        $pdf     = PDF::loadView('invoice_template_final', ['invoice' => $transformed]);
        $pdfPath = 'pdf_invoice_' . ($transformed['invoice_number']) . '.pdf';

        return $pdf->stream($pdfPath);

    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'invoice_start_number' => 'required|integer',
            'invoice_end_number'   => 'required|integer',
        ]);

        $startNumber = $request->invoice_start_number;
        $endNumber   = $request->invoice_end_number;

        Invoice::whereBetween('id', [$startNumber, $endNumber])->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoices deleted successfully.');
    }
}
