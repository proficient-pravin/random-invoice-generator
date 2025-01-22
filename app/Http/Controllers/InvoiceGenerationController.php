<?php

namespace App\Http\Controllers;

// use Maatwebsite\Excel\Facades\Excel;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;

class InvoiceGenerationController extends Controller
{

   // Declare global properties
   protected $allCustomers;
   protected $allProducts;
   protected $shuffledCustomers = [];
   protected $shuffledProducts = [];
   protected $customerIndex = 0;
   protected $productIndex = 0;

    /**
     * Constructor to initialize data.
     */
    public function __construct()
    {
       // Load the customers and products once in the constructor
       $this->allCustomers = Customer::whereNotNull('first_name')->get()->toArray();
       $this->allProducts = Product::all()->toArray();

       // Shuffle the customers and products initially
       $this->shuffleCustomers();
       $this->shuffleProducts();
    }

    public function showForm()
    {
        // $pdf = Pdf::loadView('invoice_template_final2');
        // return $pdf->stream('invoice-' . time() . '.pdf');

        $startInvoiceNumber = Invoice::select(DB::raw('CAST(invoice_number AS UNSIGNED) as invoice_number'))
        ->orderBy('invoice_number', 'desc')
        ->value('invoice_number');
        
        $startInvoiceNumber = $startInvoiceNumber ? $startInvoiceNumber + 1 : 1;


        // return view('developer', [
        //     'startInvoiceNumber' => $startInvoiceNumber
        // ]);
        return view('invoices.generate', [
            'startInvoiceNumber' => $startInvoiceNumber,
        ]);
    }

    public function updateCsvFiles(Request $request)
    {
        $request->validate([
            'product_csv' => 'file|mimes:csv,txt|max:2048',
            'customer_csv' => 'file|mimes:csv,txt|max:2048',
        ]);

        try {
            // Define the file paths in the public folder
            $productCsvPath = public_path('products.csv');
            $customerCsvPath = public_path('customers.csv');

            // Handle the product CSV upload
            if ($request->hasFile('product_csv')) {
                $request->file('product_csv')->move(public_path(), 'products.csv');
            }

            // Handle the customer CSV upload
            if ($request->hasFile('customer_csv')) {
                $request->file('customer_csv')->move(public_path(), 'customers.csv');
            }

            return redirect()->back()->with('success', 'CSV files have been successfully updated.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function generateInvoices(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1'); // Unlimited memory

        DB::beginTransaction();
        try {

            // Validate user input
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'start_invoice_number' => 'required|integer',
                'num_invoices' => 'nullable|integer|max:500',
                'total_amount' => 'required',
            ]);

            $totalInvoiceAmount = $request->total_amount;
            $totalNumberOfInvoiceToBeGenerated = $request->num_invoices;
            $invoiceSequenceStartFrom = $request->start_invoice_number;

            // $invoices = $this->generateInvoiceData(
            //     floatval($totalInvoiceAmount),
            //     $totalNumberOfInvoiceToBeGenerated,
            //     $invoiceSequenceStartFrom
            // );

            if (empty(request()->num_invoices)) {
                $invoices = $this->generateInvoiceDataV2(
                    floatval($totalInvoiceAmount),
                    $invoiceSequenceStartFrom
                );
            } else {
                $invoices = $this->generateInvoiceData(
                    floatval($totalInvoiceAmount),
                    $totalNumberOfInvoiceToBeGenerated,
                    $invoiceSequenceStartFrom
                );
            }

            // Delete existing ZIP files in the directory
            $zipFiles = public_path('*.zip');
            $existingZipFiles = File::glob($zipFiles);
            foreach ($existingZipFiles as $file) {
                File::delete($file); // Delete each file
            }

            Cache::put('start_invoice_number', request()->start_invoice_number + count($invoices));

            // Calculate the total amount of generated invoices
            $totalGeneratedAmount = str_replace(",", "", array_sum(array_column($invoices, 'total')));
            $this->storeInvoices($invoices);
            DB::commit();
            return $this->generateInvoicesZip($invoices, "invoice_total-$totalGeneratedAmount.zip");

            $invoice = collect($invoices[0]);
            // Pass data to the Blade view for PDF generation
            $pdf = Pdf::loadView('invoice_template_final', compact('invoice'))->setPaper([0, 0, 612, 792], 'portrait');

            return $pdf->stream('invoice-' . time() . '.pdf');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    private function generateInvoiceItems(
        float $targetAmount,
        string $productItemName,
        string $productItemDescription,
        float $productUnitPrice,
        float $taxPercentage
    ): array {
        $items = [];
        $remainingAmount = $targetAmount;
        $usedProducts = [];
        $numberOfItems = rand(1, 2);

        for ($i = 0; $i < $numberOfItems; $i++) {
            $isLastItem = ($i == $numberOfItems - 1);

            do {
                $product = $this->getRandomProduct();
            } while (
                in_array($product['product_name'], $usedProducts) ||
                floatval($product['unit_price']) <= 0
            );

            $usedProducts[] = $product['product_name'];

            $unitPrice = floatval($product['unit_price']);
            $quantity = $isLastItem
            ? max(1, min(4, ceil($remainingAmount / $unitPrice)))
            : rand(1, 4);

            // Adjust the quantity based on the unit price to ensure the total is at least $100
            while ($unitPrice * $quantity < 100) {
                $quantity++;
            }

            $amount = round($quantity * $unitPrice, 2);
            $tax = round($amount * ($taxPercentage / 100), 2);

            $items[] = [
                'name' => $product['product_name'],
                'description' => $product['product_name'],
                'quantity' => number_format($quantity, 2, '.', ','),
                'unit_price' => number_format($unitPrice, 2, '.', ','),
                'tax' => number_format($tax, 2, '.', ','),
                'tax_percentage' => $taxPercentage,
                'amount' => number_format($amount, 2, '.', ','),
            ];

            $remainingAmount = max(0, $remainingAmount - $amount);
        }

        return $items;
    }

    private function generateRandomAmount(
        float $averageAmount,
        float $remainingTotal,
        int $remainingCount
    ): float {
        if ($remainingCount <= 1) {
            return round($remainingTotal, 2); // Last invoice takes the remaining amount
        }

        // Calculate bounds with safeguards
        $minAmount = max(0.01, $averageAmount * 0.9); // Minimum cannot be less than 0.01
        $maxAmount = min($averageAmount * 1.1, $remainingTotal - ($remainingCount - 1) * 0.01);

        // Ensure $maxAmount is not less than $minAmount
        if ($maxAmount < $minAmount) {
            $maxAmount = $minAmount;
        }

        // Generate a random amount within valid bounds
        return round(mt_rand($minAmount * 100, $maxAmount * 100) / 100, 2);
    }

    private function generateInvoiceData(
        float $totalInvoiceAmount,
        int $totalNumberOfInvoiceToBeGenerated,
        int $invoiceSequenceStartFrom
    ): array {
        $invoices = [];
        $remainingAmount = $totalInvoiceAmount;
        $averageInvoiceAmount = $totalInvoiceAmount / $totalNumberOfInvoiceToBeGenerated;

        // Generate invoices with small variations to make the total amount close to the requested amount
        for ($i = 0; $i < $totalNumberOfInvoiceToBeGenerated; $i++) {
            $isLastInvoice = ($i == $totalNumberOfInvoiceToBeGenerated - 1);

            // Control the invoice amount deviation for all invoices except the last one
            $currentInvoiceAmount = ($isLastInvoice)
            ? $remainingAmount
            : $this->generateRandomAmount($averageInvoiceAmount, $remainingAmount, $totalNumberOfInvoiceToBeGenerated - $i);

            // Random customer and product for each invoice
            $customer = $this->getRandomCustomer();
            $product = $this->getRandomProduct();
            $taxPercentage = request()->tax_percentage;

            $invoiceItems = $this->generateInvoiceItems(
                $currentInvoiceAmount,
                $product['product_name'],
                $product['product_name'],
                floatval($product['unit_price']),
                floatval($taxPercentage)
            );

            $subtotal = array_sum(array_column($invoiceItems, 'amount'));
            $totalTax = array_sum(array_column($invoiceItems, 'tax'));

            $invoices[] = [
                 ...$customer,
                'invoice_number' => $invoiceSequenceStartFrom + $i,
                'invoice_date' => $this->getRandomDate($totalNumberOfInvoiceToBeGenerated, $i),
                'invoice_items' => $invoiceItems,
                'subtotal' => number_format($subtotal, 2, '.', ','),
                'total_tax' => number_format($totalTax, 2, '.', ','),
                'total' => number_format(($subtotal + $totalTax), 2, '.', ','),
            ];

            // Update remaining amount
            $remainingAmount -= $currentInvoiceAmount;
        }

        return $invoices;
    }

    private function generateInvoiceDataV2(
        float $totalInvoiceAmount,
        int $invoiceSequenceStartFrom
    ): array {
        $invoices = [];
        $remainingAmount = $totalInvoiceAmount;
        $totalGeneratedAmount = 0; // Track the total generated amount

        $i = 0;
        while ($remainingAmount > 0) {
            // Random customer and product for each invoice
            $customer = $this->getRandomCustomer();
            $product = $this->getRandomProduct();
            $taxPercentage = request()->tax_percentage;
            // Generate invoice items
            $invoiceItems = $this->generateInvoiceItems(
                $remainingAmount,
                $product['product_name'],
                $product['product_name'],
                floatval($product['unit_price']),
                floatval($taxPercentage)
            );

            $subtotal = array_sum(array_column($invoiceItems, 'amount'));
            $totalTax = array_sum(array_column($invoiceItems, 'tax'));

            // Calculate total for the current invoice
            $currentInvoiceAmount = round($subtotal + $totalTax, 2);

            // Update total generated amount and remaining amount
            $totalGeneratedAmount += $currentInvoiceAmount;
            $remainingAmount = max(0, $totalInvoiceAmount - $totalGeneratedAmount);

            // Add invoice to the list
            $invoices[] = [
                 ...$customer,
                'invoice_number' => $invoiceSequenceStartFrom++,
                'invoice_date' => $this->getRandomDate($totalInvoiceAmount, $i),
                'invoice_items' => $invoiceItems,
                'subtotal' => number_format($subtotal, 2, '.', ','),
                'total_tax' => number_format($totalTax, 2, '.', ','),
                'total' => number_format(($subtotal + $totalTax), 2, '.', ','),
            ];
            $i++;
        }

        return $invoices;
    }

    public function getRandomDate(
        int $totalNumberOfInvoices,
        int $invoiceIndex
    ): string {
        $startDate = strtotime(request()->start_date);
        $endDate = strtotime(request()->end_date);

        if (!$startDate || !$endDate) {
            return null;
        }

        // Calculate the total number of days in the range
        $totalDays = floor(($endDate - $startDate) / (60 * 60 * 24)) + 1;
        if ($totalDays < 1) {
            return null; // Invalid date range
        }

        // Calculate how many invoices should be assigned to each day
        $invoicesPerDay = intdiv($totalNumberOfInvoices, $totalDays);
        $remainingInvoices = $totalNumberOfInvoices % $totalDays;

        // Determine the index of the day this invoice should fall on
        $dayIndex = intdiv($invoiceIndex, $invoicesPerDay);
        if ($invoiceIndex % $invoicesPerDay < $remainingInvoices) {
            $dayIndex++;
        }

        // Calculate the random date based on the index of the day
        $randomDate = date('Y-m-d', strtotime("+$dayIndex days", $startDate));
        return $randomDate;
    }

    /**
     * Get the next random customer in sequence.
     */
    public function getRandomCustomer(): ?array
    {
        // If we've reached the end of the shuffled list, reshuffle
        if ($this->customerIndex >= count($this->shuffledCustomers)) {
            $this->shuffleCustomers();
        }

        // Get the current customer
        $selectedCustomer = $this->shuffledCustomers[$this->customerIndex];

        // Increment the index for the next call
        $this->customerIndex++;

        return [
            'customer_id' => $selectedCustomer['id'] ?? '',
            'email' => $selectedCustomer['email'] ?? '',
            'first_name' => $selectedCustomer['first_name'] ?? '',
            'last_name' => $selectedCustomer['last_name'] ?? '',
            'full_name' => "{$selectedCustomer['first_name']} {$selectedCustomer['last_name']}",
            'po_attention_to' => "{$selectedCustomer['first_name']} {$selectedCustomer['last_name']}",
            'po_address_line1' => $selectedCustomer['po_address_line1'] ?? '',
            'po_address_line2' => $selectedCustomer['po_address_line2'] ?? '',
            'po_address_line3' => $selectedCustomer['po_address_line3'] ?? '',
            'po_address_line4' => $selectedCustomer['po_address_line4'] ?? '',
            'po_city' => $selectedCustomer['po_city'] ?? '',
            'po_region' => $selectedCustomer['po_region'] ?? '',
            'po_zip_code' => $selectedCustomer['po_zip_code'] ?? '',
            'po_country' => $selectedCustomer['po_country'] ?? '',
            'sa_attention_to' => $selectedCustomer['sa_attention_to'] ?? '',
            'sa_address_line1' => $selectedCustomer['sa_address_line1'] ?? '',
            'sa_address_line2' => $selectedCustomer['sa_address_line2'] ?? '',
            'sa_address_line3' => $selectedCustomer['sa_address_line3'] ?? '',
            'sa_address_line4' => $selectedCustomer['sa_address_line4'] ?? '',
            'sa_city' => $selectedCustomer['sa_city'] ?? '',
            'sa_region' => $selectedCustomer['sa_region'] ?? '',
            'sa_zip_code' => $selectedCustomer['sa_zip_code'] ?? '',
            'sa_country' => $selectedCustomer['sa_country'] ?? '',
        ];
    }

    /**
     * Get the next random product in sequence.
     */
    public function getRandomProduct(): ?array
    {
        // If we've reached the end of the shuffled list, reshuffle
        if ($this->productIndex >= count($this->shuffledProducts)) {
            $this->shuffleProducts();
        }

        // Get the current product
        $product = $this->shuffledProducts[$this->productIndex];

        // Increment the index for the next call
        $this->productIndex++;

        return $product;
    }

    /**
     * Generate invoices as PDFs, bundle them into a ZIP file, and return for download.
     *
     * @param array $invoices Array of invoice data.
     * @param string $zipFileName Name of the ZIP file.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generateInvoicesZip(array $invoices, string $zipFileName = 'invoices.zip')
    {
        $zip = new ZipArchive;
        $zipPath = public_path($zipFileName);

        // Open the ZIP file for writing
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $htmlContent = '';
            foreach ($invoices as $index => $invoice) {

                $invoice['print_address_line1'] = request()->print_address_line1 ?? null;
                $invoice['print_address_line2'] = request()->print_address_line2 ?? null;
                // Generate PDF for the invoice
                $pdf = PDF::loadView('invoice_template_final', ['invoice' => $invoice]);
                $pdfPath = 'pdf_invoice_' . ($index + 1) . '.pdf';
                $htmlContent .= view('invoice_template_final', ['invoice' => $invoice])->render();

                // // Add a page break after each invoice (if needed)
                // $htmlContent .= '<div style="page-break-after: always;"></div>';

                // Add the PDF to the ZIP
                $zip->addFromString($pdfPath, $pdf->output());
            }

            // Generate a single PDF from the combined HTML
            $pdf = PDF::loadHTML($htmlContent);

            // Add the single PDF to the ZIP
            $pdfPath = 'all_invoices.pdf';
            $zip->addFromString($pdfPath, $pdf->output());

            // Generate CSV for the invoice
            $csvData = $this->generateInvoiceCsv($invoices);
            $csvPath = 'csv_invoice.csv';
            // Add the CSV to the ZIP
            $zip->addFromString($csvPath, $csvData);

            // Close the ZIP file
            $zip->close();

            // Return the ZIP file as a response for download
            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        // Handle error if ZIP creation fails
        return response()->json(['error' => 'Failed to create ZIP file'], 500);
    }

    /**
     * Generate CSV content for an invoice.
     *
     * @param array $invoice Invoice data.
     * @return string CSV content.
     */
    private function generateInvoiceCsv(array $invoices): string
    {
        $csvHeader = [
            'ContactName', 'EmailAddress', 'POAddressLine1', 'POAddressLine2', 'POAddressLine3', 'POAddressLine4',
            'POCity', 'PORegion', 'POPostalCode', 'POCountry', 'InvoiceNumber', 'Reference', 'InvoiceDate',
            'DueDate', 'Description', 'Quantity', 'UnitAmount', 'Discount', 'TaxAmount', 'Total',
        ];

        $csvRows = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice['invoice_items'] as $item) {
                $firstName = $invoice['first_name'];
                $lastName = $invoice['last_name'];
                $fullName =  $firstName . ' ' . $lastName;
                $csvRows[] = [
                    $fullName,
                    $invoice['email'] ?? '',
                    $invoice['po_address_line1'] ?? '',
                    $invoice['po_address_line2'] ?? '',
                    $invoice['po_address_line3'] ?? '',
                    $invoice['po_address_line4'] ?? '',
                    $invoice['po_city'] ?? '',
                    $invoice['po_region'] ?? '',
                    $invoice['po_zip_code'] ?? '',
                    $invoice['po_country'] ?? '',
                    $invoice['invoice_number'] ?? '',
                    '', // Reference field is empty in the provided data
                    $invoice['invoice_date'] ?? '',
                    $invoice['invoice_date'] ?? '', // Assuming DueDate matches InvoiceDate
                    $item['description'] ?? '',
                    $item['quantity'] ?? '',
                    $item['unit_price'] ?? '',
                    '', // Discount field is empty in the provided data
                    $item['tax'] ?? '',
                    $item['amount'] ?? '',
                ];
            }
        }

        // Open a memory stream to write CSV data
        $stream = fopen('php://memory', 'r+');
        fputcsv($stream, $csvHeader);

        foreach ($csvRows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        return $csvContent;
    }

    /**
     * Store the invoice and its associated items.
     */
    public function storeInvoices($invoices)
    {
        // Begin database transaction to ensure atomicity
        DB::beginTransaction();

        try {

            foreach ($invoices as $_invoices){
                // Create the invoice
                $invoice = Invoice::create([
                    'customer_id' => $_invoices['customer_id'],
                    'invoice_number' => $_invoices['invoice_number'],
                    'invoice_date' => $_invoices['invoice_date'],
                ]);
    
                // Create invoice items and associate them with the created invoice
                foreach ($_invoices['invoice_items'] as $itemData) {
                    $invoice->items()->create([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'],
                        'quantity' => floatval(str_replace(",","",$itemData['quantity'])),
                        'unit_price' => floatval(str_replace(",","",$itemData['unit_price'])),
                        'tax' => floatval(str_replace(",","",$itemData['tax'])),
                        'tax_percentage' => floatval(str_replace(",","",$itemData['tax_percentage'])),
                        'amount' => floatval(str_replace(",","",$itemData['amount'])),
                    ]);
                }
            }

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error creating invoice: ' . $e->getMessage());
        }
    }

    /**
     * Shuffle the customers.
     */
    protected function shuffleCustomers(): void
    {
        $this->shuffledCustomers = $this->allCustomers;
        shuffle($this->shuffledCustomers); // Randomly shuffle the customers
        $this->customerIndex = 0; // Reset the index
    }

    /**
     * Shuffle the products.
     */
    protected function shuffleProducts(): void
    {
        $this->shuffledProducts = $this->allProducts;
        shuffle($this->shuffledProducts); // Randomly shuffle the products
        $this->productIndex = 0; // Reset the index
    }
}
