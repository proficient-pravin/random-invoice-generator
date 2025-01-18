<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tag;
use Illuminate\Http\Request;
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
                    'total_invoice_amount' => Invoice::selectRaw('SUM(invoice_items.amount)')
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
            unset($customers[0]);
            $customers = array_values($customers);

            $filteredCustomers = array_filter($customers, function ($customer) {
                return ! empty($customer[2]) && ! empty($customer[3]) && ! empty($customer[4]) && ! empty($customer[5]) && ! empty($customer[6]);
            });

            $preparedCustomers = [];
            foreach ($filteredCustomers as $customer) {
                $_customer = [
                    'email'            => $customer[2] ?? '',
                    'first_name'       => $customer[3] ?? '',
                    'last_name'        => $customer[4] ?? '',
                    'po_attention_to'  => $customer[5] ?? '',
                    'po_address_line1' => $customer[6] ?? '',
                    'po_address_line2' => $customer[7] ?? '',
                    'po_address_line3' => $customer[8] ?? '',
                    'po_address_line4' => $customer[9] ?? '',
                    'po_city'          => $customer[10] ?? '',
                    'po_region'        => $customer[11] ?? '',
                    'po_zip_code'      => $customer[12] ?? '',
                    'po_country'       => $customer[13] ?? '',
                    'sa_address_line1' => $customer[15] ?? '',
                    'sa_address_line2' => $customer[16] ?? '',
                    'sa_address_line3' => $customer[17] ?? '',
                    'sa_address_line4' => $customer[18] ?? '',
                    'sa_city'          => $customer[19] ?? '',
                    'sa_region'        => $customer[20] ?? '',
                    'sa_zip_code'      => $customer[21] ?? '',
                    'sa_country'       => $customer[22] ?? '',
                ];
                array_push($preparedCustomers, $_customer);
                Customer::updateOrCreate(
                    ['email' => $_customer['email']],
                    $_customer
                );
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
            'value'  => 'required|string|max:255',
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
            $customer->{$request->column} = $request->value;
        }

        // Save the customer
        $customer->save();

        return response()->json(['success' => true]);
    }

}
