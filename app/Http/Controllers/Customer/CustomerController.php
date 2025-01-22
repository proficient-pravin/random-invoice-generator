<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index()
    {

        if (request()->ajax()) {
            $customers = Customer::query()
                ->with('tag')
                ->when(! empty(request()->tag), function ($q) {
                    $q->whereHas('tag', function ($q) {
                        $q->whereIn('id', request()->tag);
                    });
                })                      // Load tag relationship
                ->select('customers.*') // Select customer columns
                ->addSelect([
                    'total_invoice_amount' => Invoice::selectRaw('SUM(invoice_items.amount + invoice_items.tax)')
                        ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
                        ->whereColumn('invoices.customer_id', 'customers.id')
                        ->groupBy('invoices.customer_id')
                        ->limit(1), // Ensure only one value is returned per customer
                ])
                ->get();

            return DataTables::of($customers)
                ->addColumn('actions', function ($customer) {
                    return view('customers.actions', compact('customer'));
                })
                ->addColumn('full_name', function ($customer) {
                    return "$customer->first_name $customer->last_name";
                })
                ->addColumn('tag_name', function ($customer) {
                    $tag = $customer->tag;
                    if ($tag) {
                        return view('customers.tag', compact('tag'));
                    }
                    return ''; // Return empty if no tag is present
                })
                ->addColumn('total_invoice_amount', function ($customer) {
                    return number_format($customer->total_invoice_amount, 2, '.', ',');
                })
                ->make(true);
        }

        return view('customers.index', [
            'tags' => Tag::all(),
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create', [
            'tags' => Tag::all(),
        ]);
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'            => 'required|email|unique:customers,email',
            'tag_id'           => 'nullable',
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',

            'po_address_line1' => 'required|string|max:255',
            'po_city'          => 'required|string|max:255',
            'po_zip_code'      => 'required|string|max:10',
            'po_country'       => 'required|string|max:255',

            'sa_address_line1' => 'required|string|max:255',
            'sa_city'          => 'required|string|max:255',
            'sa_zip_code'      => 'required|string|max:10',
            'sa_country'       => 'required|string|max:255',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully!');
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param \App\Models\Customer $customer
     */
    public function view(Customer $customer)
    {
        // Retrieve the customer with additional calculated column and related data
        $customer = Customer::query()
            ->where('id', $customer->id)                  // Ensure we are working with the correct customer
            ->with(['tag', 'invoices', 'invoices.items']) // Eager load relationships
            ->addSelect([
                'total_invoice_amount' => Invoice::selectRaw('SUM(invoice_items.amount + invoice_items.tax)')
                    ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
                    ->whereColumn('invoices.customer_id', 'customers.id')
                    ->groupBy('invoices.customer_id')
                    ->limit(1), // Ensure only one value is returned per customer
            ])
            ->first();

        $invoiceData = Invoice::selectRaw('SUM(invoice_items.amount + invoice_items.tax) as total_invoice_amount')
            ->selectRaw('DATE_FORMAT(invoices.invoice_date, "%m") AS month')
            ->selectRaw('DATE_FORMAT(invoices.invoice_date, "%Y") AS year')
            ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.customer_id', $customer->id)
            ->groupBy(DB::raw('DATE_FORMAT(invoices.invoice_date, "%Y")'), DB::raw('DATE_FORMAT(invoices.invoice_date, "%m")'))
            ->get();

        // Initialize an empty array to store the final result
        $finalResult = [];

        // Create an associative array of invoice data for easier lookup
        $invoiceDataArray = $invoiceData->keyBy(function ($item) {
            return $item->month . '-' . $item->year;
        });

        // Loop for the next 3 months (from +1 to +3)
        for ($i = 1; $i > 0; $i--) {
            $last12Months[] = date('m-Y', strtotime("+$i month"));
        }
        // Loop for the past 12 months (from 0 to -11)
        for ($i = 0; $i < 12; $i++) {
            $last12Months[] = date('m-Y', strtotime("-$i month"));
        }

        // Loop through the last 12 months and check if data exists
        foreach (array_reverse($last12Months) as $monthYear) {
            if (isset($invoiceDataArray[$monthYear])) {
                $finalResult[$monthYear] = $invoiceDataArray[$monthYear]->total_invoice_amount;
            } else {
                $finalResult[$monthYear] = 0;
            }
        }

        // Query to fetch daily invoice data for the customer
        $dailyInvoiceData = Invoice::selectRaw('SUM(invoice_items.amount + invoice_items.tax) as total_invoice_amount')
            ->selectRaw('DATE_FORMAT(invoices.invoice_date, "%Y-%m-%d") AS date') // Use DATE_FORMAT for MySQL
            ->join('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.customer_id', $customer->id)
            ->groupBy(DB::raw('DATE_FORMAT(invoices.invoice_date, "%Y-%m-%d")')) // Group by formatted date
            ->get();

        // Create an associative array of invoice data for easier lookup
        $dailyInvoiceDataArray = $dailyInvoiceData->keyBy(function ($item) {
            return $item->date;
        });

        // Initialize the daily range for the chart
        $dailyChartData = [
            'labels' => [],
            'data'   => [],
        ];

        $startDate = now()->subDays(30); // 12 days ago

        for ($i = 0; $i <= 15; $i++) {
            $currentDate                = $startDate->addDay()->format('Y-m-d');
            $dailyChartData['labels'][] = $currentDate;                                                                                                 // Add the date as a label
            $dailyChartData['data'][]   = isset($dailyInvoiceDataArray[$currentDate]) ? $dailyInvoiceDataArray[$currentDate]->total_invoice_amount : 0; // Add invoice data or 0
        }

        return view('customers.view', [
            'customer'       => $customer,
            'chartData'      => [
                'labels' => array_keys($finalResult),
                'data'   => array_values($finalResult),
            ],
            'dailyChartData' => $dailyChartData,
        ]);
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param \App\Models\Customer $customer
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', [
            'customer' => $customer,
            'tags'     => Tag::all(),
        ]);
    }

    /**
     * Update the specified customer in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Customer $customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'email'            => 'required|email|unique:customers,email,' . $customer->id,
            'tag_id'           => 'nullable',
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',

            'po_address_line1' => 'required|string|max:255',
            'po_city'          => 'required|string|max:255',
            'po_zip_code'      => 'required|string|max:10',
            'po_country'       => 'required|string|max:255',

            'sa_address_line1' => 'required|string|max:255',
            'sa_city'          => 'required|string|max:255',
            'sa_zip_code'      => 'required|string|max:10',
            'sa_country'       => 'required|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
    }

    /**
     * import customer.
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|max:10000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Process the CSV file
            $file      = $request->file('import_file');
            $customers = array_map('str_getcsv', file($file));
            // Extract the header (first row)
            $header = $customers[0];
            
            // Map the header with the subsequent rows
            $mappedData = array_filter(array_map(function ($row) use ($header) {                
                if (count($header) !== count($row)) {
                    // If the counts don't match, ignore this row
                    return null;
                }
                return array_combine($header, $row);
            }, array_slice($customers, 1)));

            $filteredCustomers = array_filter($mappedData, function ($customer) {
                return !empty($customer['Contact First Name']);
            });

            $preparedCustomers = [];
            foreach ($filteredCustomers as $customer) {
                $_customer = [
                    'email'            => $customer['Primary Contact Email'] ?? '',
                    'first_name'       => $customer['Contact First Name'] ?? '',
                    'last_name'        => $customer['Contact Last Name'] ?? '',
                    'po_attention_to'  => $customer['Contact First Name'] ." ". $customer['Contact Last Name'] ?? '',
                    'po_address_line1' => $customer['52 Fadem Road'] ?? '',
                    'po_address_line2' => $customer['Street 1'] ?? '',
                    // 'po_address_line3' => $customer[8] ?? '',
                    // 'po_address_line4' => $customer[9] ?? '',
                    'po_city'          => $customer['City'] ?? '',
                    'po_region'        => $customer['State'] ?? '',

                    'po_zip_code'      => $customer['ZIP'] ?? '',
                    'po_country'       => $customer['Country'] ?? '',

                    // 'sa_address_line1' => $customer[15] ?? '',
                    // 'sa_address_line2' => $customer[16] ?? '',
                    // 'sa_address_line3' => $customer[17] ?? '',
                    // 'sa_address_line4' => $customer[18] ?? '',
                    // 'sa_city'          => $customer[19] ?? '',
                    // 'sa_region'        => $customer[20] ?? '',
                    // 'sa_zip_code'      => $customer[21] ?? '',
                    // 'sa_country'       => $customer[22] ?? '',
                ];
                array_push($preparedCustomers, $_customer);
                try {
                    if (!empty(trim($_customer['email']))) {
                        Customer::updateOrCreate(
                            [
                                'email' => $_customer['email'] ?: null, // Default to null if email is empty
                            ],
                            $_customer
                        );
                    } else {
                        Customer::updateOrCreate(
                            [
                                'first_name' => trim($_customer['first_name']),
                                'last_name' => trim($_customer['last_name']),
                            ],
                            $_customer
                        );
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->getCode() === '23000') {
                        Log::error('Duplicate entry detected for customer', [
                            'error' => $e->getMessage(),
                            'customer' => $_customer,
                        ]);
                    }
                }             
            }


            return redirect()->route('customers.index')->with('success', 'Customers imported successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['import_file' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified customer from storage.
     *
     * @param \App\Models\Customer $customer
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully!');
    }

    public function updateInline(Request $request)
    {
        // Validate input
        $request->validate([
            'id'     => 'required|exists:customers,id',
            'column' => 'required',
            'value'  => 'max:255',
        ]);

        // Check if updating the email and ensure uniqueness
        if ($request->column === 'email') {
            $request->validate([
                'value' => 'email|unique:customers,email,' . $request->id,
            ]);
        }

        $customer = Customer::find($request->id);

        if ($request->column === 'full_name') {
            // Handle splitting of full_name into first_name and last_name
            $nameParts            = explode(' ', trim($request->value), 2);
            $customer->first_name = $nameParts[0];
            $customer->last_name  = isset($nameParts[1]) ? $nameParts[1] : '';
        } else {
            // Update the specified column dynamically
            $customer->{$request->column} = $request->value ?? '';
        }

        // Save the customer
        $customer->save();

        return response()->json(['success' => true]);
    }

}
