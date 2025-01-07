<?php

namespace App\Http\Controllers;


use Barryvdh\DomPDF\Facade\Pdf as PDF;
// use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use ZipArchive;

class InvoiceGenerationController extends Controller
{
    public function showForm()
    {
        return view('invoice_form');
    }

    

    // public function generateInvoices(Request $request)
    // {
    //     // Validate user input
    //     $validated = $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date',
    //         'start_invoice_number' => 'required|integer',
    //         'num_invoices' => 'required|integer',
    //         'total_amount' => 'required|numeric',
    //     ]);

    //     $invoicesData = [];
    //     $zip = new ZipArchive;
    //     $zipFileName = 'invoices_' . time() . '.zip';

    //     // Open the ZIP file for writing
    //     if ($zip->open(public_path($zipFileName), ZipArchive::CREATE) === TRUE) {
    //         $remainingAmount = $validated['total_amount'];

    //         // Create an instance of Faker to generate random data
    //         $faker = Faker::create();

    //         for ($i = 0; $i < $validated['num_invoices']; $i++) {
    //             // Generate random invoice data
    //             $invoiceData = $this->generateRandomInvoiceData(
    //                 $validated['start_invoice_number'] + $i,
    //                 $remainingAmount / ($validated['num_invoices'] - $i), // Distribute amount evenly
    //                 $validated['start_date'],
    //                 $validated['end_date'],
    //                 $faker
    //             );

    //             // Create the PDF
    //             $pdf = PDF::loadView('invoice_template', ['invoice' => $invoiceData]);
    //             $pdfPath = 'invoices/invoice_' . ($validated['start_invoice_number'] + $i) . '.pdf';

    //             // Save the PDF to the ZIP
    //             $zip->addFromString($pdfPath, $pdf->output());

    //             // Collect invoice data for Excel export
    //             $invoicesData[] = $invoiceData;

    //             // Deduct the generated amount from the remaining amount
    //             $remainingAmount -= $invoiceData['total'];
    //         }

    //         // Close the ZIP file
    //         $zip->close();

    //         return response()->download(public_path($zipFileName));
    //     }

    //     return response()->json(['error' => 'Failed to create ZIP file'], 500);
    // }

    // private function generateRandomInvoiceData($invoiceNumber, $amount, $startDate, $endDate, $faker)
    // {
    //     // Generate random customer (user) data
    //     $customerName = $faker->name;
    //     $customerEmail = $faker->email;
    //     $customerPhone = $faker->phoneNumber;

    //     // Generate random product data
    //     $productName = $faker->word;
    //     $productPrice = $amount;
    //     $quantity = rand(1, 5);

    //     // Calculate tax (assuming 10% tax)
    //     $taxRate = 0.10;
    //     $taxAmount = $amount * $taxRate;
    //     $totalAmount = $amount + $taxAmount;

    //     // Create invoice data array
    //     return [
    //         'invoice_number' => $invoiceNumber,
    //         'customer_name' => $customerName,
    //         'customer_email' => $customerEmail,
    //         'customer_phone' => $customerPhone,
    //         'product_name' => $productName,
    //         'product_price' => $productPrice,
    //         'quantity' => $quantity,
    //         'amount' => $amount,
    //         'tax' => $taxAmount,
    //         'tax_rate' => $taxRate,
    //         'total' => $totalAmount,
    //         'invoice_date' => now()->toDateString(),
    //     ];
    // }
}