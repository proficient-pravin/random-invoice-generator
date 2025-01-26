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
                ->addSelect(\DB::raw('ROUND(SUM(invoice_items.amount), 2) as sub_total'))                           // Calculate the total dynamically from invoice items
                ->addSelect(\DB::raw('ROUND(SUM(invoice_items.tax), 2) as tax'))                                    // Calculate the total dynamically from invoice items
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
            "invoice_number"      => $invoice['invoice_number'],
            "customer_id"         => $invoice['customer_id'],
            "invoice_date"        => $invoice['invoice_date'],
            "print_address_line1" => $invoice['print_address_line1'],
            "print_address_line2" => $invoice['print_address_line2'],
            "print_address_line3" => $invoice['print_address_line3'],
            "print_address_line4" => $invoice['print_address_line4'],
            "created_at"          => $invoice['created_at'],
            "updated_at"          => $invoice['updated_at'],
            "invoice_items"       => $invoice['items'], // Renaming items to invoice_items
            'subtotal'            => number_format($subtotal, 2, '.', ','),
            'total_tax'           => number_format($totalTax, 2, '.', ','),
            'total'               => number_format(($subtotal + $totalTax), 2, '.', ','),
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
            "invoice_number"      => $invoice['invoice_number'],
            "customer_id"         => $invoice['customer_id'],
            "invoice_date"        => $invoice['invoice_date'],
            "invoice_time"        => $invoice['invoice_time'],
            "print_address_line1" => $invoice['print_address_line1'],
            "print_address_line2" => $invoice['print_address_line2'],
            "print_address_line3" => $invoice['print_address_line3'],
            "print_address_line4" => $invoice['print_address_line4'],
            "created_at"          => $invoice['created_at'],
            "updated_at"          => $invoice['updated_at'],
            "invoice_items"       => $invoice['items'], // Renaming items to invoice_items
            'subtotal'            => number_format($subtotal, 2, '.', ','),
            'total_tax'           => number_format($totalTax, 2, '.', ','),
            'total'               => number_format(($subtotal + $totalTax), 2, '.', ','),
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

    public function export(Request $request)
    {
        $request->validate([
            'export_daterange' => 'required|string',
            'output_type'      => 'required|array',
        ]);

        $daterange = explode(' - ', $request->export_daterange);
        $startDate = $daterange[0];
        $endDate   = $daterange[1];

        $invoices = Invoice::with('items')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->when(! empty(request()->customer), function ($q) {
                $q->whereHas('customer', function ($q) {
                    $q->whereIn('id', request()->customer);
                });
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('invoice_date', [$startDate, $endDate]);
            })
            ->select(
                'customers.id as customer_id',
                'customers.email',
                'customers.first_name',
                'customers.last_name',
                \DB::raw('CONCAT(customers.first_name, " ", customers.last_name) as full_name'), // Concatenate first and last name
                'customers.po_attention_to',
                'customers.po_address_line1',
                'customers.po_address_line2',
                'customers.po_address_line3',
                'customers.po_address_line4',
                'customers.po_city',
                'customers.po_region',
                'customers.po_zip_code',
                'customers.po_country',
                'customers.sa_address_line1',
                'customers.sa_address_line2',
                'customers.sa_address_line3',
                'customers.sa_address_line4',
                'customers.sa_city',
                'customers.sa_region',
                'customers.sa_zip_code',
                'customers.sa_country',

                'invoices.id',
                'invoices.invoice_number',
                'invoices.invoice_date',
                'invoices.invoice_time',
                'invoices.customer_id',
                'invoices.print_address_line1',
                'invoices.print_address_line2',
                'invoices.print_address_line3',
                'invoices.print_address_line4',
            )
            ->addSelect(\DB::raw('ROUND(SUM(invoice_items.amount + invoice_items.tax), 2) as total'))
            ->addSelect(\DB::raw('ROUND(SUM(invoice_items.amount), 2) as subtotal'))
            ->addSelect(\DB::raw('ROUND(SUM(invoice_items.tax), 2) as total_tax'))
            ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->groupBy('invoices.id')
            ->limit(50)
            ->get()
            ->map(function ($invoice) {
                $invoice->invoice_items = $invoice->items->map(function ($item) {
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
                unset($invoice->items);
                return $invoice;
            })
            ->toArray();

        return $this->generateInvoicesZip($invoices, "invoice_export.zip");

    }
}
