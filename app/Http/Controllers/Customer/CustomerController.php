<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index()
    {
        
        if (request()->ajax()) {
            $customers = Customer::query();
            return DataTables::of($customers)
                ->addColumn('actions', function ($customer) {
                    return view('customers.actions', compact('customer'));
                })
                ->make(true);
        }
    
        return view('customers.index');
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:customers,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',

            'po_address_line1' => 'required|string|max:255',
            'po_city' => 'required|string|max:255',
            'po_zip_code' => 'required|string|max:10',
            'po_country' => 'required|string|max:255',

            'sa_address_line1' => 'required|string|max:255',
            'sa_city' => 'required|string|max:255',
            'sa_zip_code' => 'required|string|max:10',
            'sa_country' => 'required|string|max:255',
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
        return view('customers.edit', compact('customer'));
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
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',

            'po_address_line1' => 'required|string|max:255',
            'po_city' => 'required|string|max:255',
            'po_zip_code' => 'required|string|max:10',
            'po_country' => 'required|string|max:255',

            'sa_address_line1' => 'required|string|max:255',
            'sa_city' => 'required|string|max:255',
            'sa_zip_code' => 'required|string|max:10',
            'sa_country' => 'required|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
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
}
