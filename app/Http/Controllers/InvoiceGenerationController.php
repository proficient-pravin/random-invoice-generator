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
        // $pdf = Pdf::loadView('invoice_template_final2');
        // return $pdf->stream('invoice-' . time() . '.pdf');
        return view('invoice_form');
    }

    // public function generateInvoices(Request $request){
    //     $customer = $this->getRandomCustomer();
    //     $product = $this->getRandomProduct();
    //     $invoiceDate = $this->getRandomDate();
    //     $taxPercentage = $product['SalesTaxRate'] == 'Tax on Sales' ? ($request->tax_percentage ?? 10) : 0;
    //     $productUnitPrice = $product['SalesUnitPrice'];
    //     $productItemName = $product['ItemName'];
    //     $productItemDescription = $product['PurchasesDescription'];
    //     $customerName = $customer['full_name'];
    //     $customerEmail = $customer['email'];
    //     $totalInvoiceAmount = $request->total_amount;
    //     $totalNumberOfInvoiceToBeGenerated = $request->num_invoices;
    //     $invoiceSequenceStartFrom = $request->start_invoice_number;

    //     $invoices = $this->generateInvoiceData(
    //         $totalInvoiceAmount,
    //         $totalNumberOfInvoiceToBeGenerated,
    //         $invoiceSequenceStartFrom,
    //         $product,
    //         $taxPercentage,
    //         $productItemName,
    //         $productItemDescription,
    //         $productUnitPrice
    //     );
    // }

    public function generateInvoices(Request $request)
    {
        $totalInvoiceAmount = $request->total_amount;
        $totalNumberOfInvoiceToBeGenerated = $request->num_invoices;
        $invoiceSequenceStartFrom = $request->start_invoice_number;

        $invoices = $this->generateInvoiceData(
            floatval($totalInvoiceAmount),
            $totalNumberOfInvoiceToBeGenerated,
            $invoiceSequenceStartFrom
        );


        // Calculate the total amount of generated invoices
        $totalGeneratedAmount = array_sum(array_column($invoices, 'total'));

        $invoice = collect($invoices[0]);
        // dd($invoice);
        // Pass data to the Blade view for PDF generation
        $pdf = Pdf::loadView('invoice_template_final', compact('invoice'));

        return $pdf->stream('invoice-' . time() . '.pdf');

        // Return the PDF to the browser
        return $pdf->download('invoice-' . time() . '.pdf');
        
        dd($invoices, $totalGeneratedAmount);
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
            $taxPercentage = $product['SalesTaxRate'] == 'Tax on Sales' ? (request()->tax_percentage ?? 10) : 0;

            $invoiceItems = $this->generateInvoiceItems(
                $currentInvoiceAmount,
                $product['ItemName'],
                $product['PurchasesDescription'],
                floatval($product['SalesUnitPrice']),
                floatval($taxPercentage)
            );

            $subtotal = array_sum(array_column($invoiceItems, 'amount'));
            $totalTax = array_sum(array_column($invoiceItems, 'tax'));

            $invoices[] = [
                ...$customer,
                'invoice_number' => $invoiceSequenceStartFrom + $i,
                'invoice_date' => $this->getRandomDate(),
                'invoice_items' => $invoiceItems,
                'subtotal' => round($subtotal, 2),
                'total_tax' => round($totalTax, 2),
                'total' => round($subtotal + $totalTax, 2),
            ];

            // Update remaining amount
            $remainingAmount -= $currentInvoiceAmount;
        }

        return $invoices;
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
        $usedProducts = []; // Track used product names
        $numberOfItems = rand(1, 4);
    
        for ($i = 0; $i < $numberOfItems; $i++) {
            $isLastItem = ($i == $numberOfItems - 1);
            $maxItemAmount = $isLastItem ? $remainingAmount : ($remainingAmount * 0.8);
    
            // Ensure a unique product with a valid unit price for each item
            do {
                $product = $this->getRandomProduct();
            } while (
                in_array($product['ItemName'], $usedProducts) || 
                floatval($product['SalesUnitPrice']) <= 0
            );
    
            $usedProducts[] = $product['ItemName']; // Add product to used list
    
            // Use the product's unit price
            $unitPrice = floatval($product['SalesUnitPrice']);
    
            // Randomly select a quantity between 1 and 4
            $quantity = $isLastItem
                ? max(1, min(4, ceil($remainingAmount / $unitPrice))) // Ensure valid quantity for the last item
                : rand(1, 4);
    
            $amount = round($quantity * $unitPrice, 2);
            $tax = round($amount * ($taxPercentage / 100), 2);
    
            $items[] = [
                'name' => $product['ItemName'],
                'description' => $product['PurchasesDescription'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax' => $tax,
                'amount' => $amount
            ];
    
            $remainingAmount -= $amount;
        }
    
        return $items;
    }

    private function generateRandomAmount(
        float $averageAmount,
        float $remainingTotal,
        int $remainingCount
    ): float {
        if ($remainingCount <= 1) {
            return $remainingTotal;
        }

        // Limit the deviation of each invoice amount to within ±10% of the average
        $minAmount = max($averageAmount * 0.9, 0.01);
        $maxAmount = min($averageAmount * 1.1, $remainingTotal - ($remainingCount - 1) * 0.01);

        return round(rand($minAmount * 100, $maxAmount * 100) / 100, 2);
    }

    function getRandomDate()
    {
        $startDate = strtotime(request()->start_date);
        $endDate = strtotime(request()->end_date);

        if (!$startDate || !$endDate) {
            return null;
        }

        // Generate random timestamp between start and end dates
        $randomTimestamp = mt_rand($startDate, $endDate);

        // Convert back to date format
        return date('Y-m-d', $randomTimestamp);
    }

    function getRandomCustomer(): ?array
    {
        $customers = $this->csvToArray('Contacts.csv');

        // Filter out customers where index 2, 3, or 4 is empty
        $filtered_customers = array_filter($customers, function ($customer) {
            return !empty($customer[2]) && !empty($customer[3]) && !empty($customer[4]) && !empty($customer[5]) && !empty($customer[6]);
        });


        $rand = array_rand($filtered_customers);

        return [
            'account_number' => $filtered_customers[$rand][1] ?? '',
            'email' => $filtered_customers[$rand][2] ?? '',
            'first_name' => $filtered_customers[$rand][3] ?? '',
            'last_name' => $filtered_customers[$rand][4] ?? '',
            'full_name' => "{$filtered_customers[$rand][3]} {$filtered_customers[$rand][4]}",
            'po_attention_to' => $filtered_customers[$rand][5] ?? '',
            'po_address_line1' => $filtered_customers[$rand][6] ?? '',
            'po_address_line2' => $filtered_customers[$rand][7] ?? '',
            'po_address_line3' => $filtered_customers[$rand][8] ?? '',
            'po_address_line4' => $filtered_customers[$rand][9] ?? '',
            'po_city' => $filtered_customers[$rand][10] ?? '',
            'po_region' => $filtered_customers[$rand][11] ?? '',
            'po_zip_code' => $filtered_customers[$rand][12] ?? '',
            'po_country' => $filtered_customers[$rand][13] ?? '',
            'sa_attention_to' => $filtered_customers[$rand][14] ?? '',
            'sa_address_line1' => $filtered_customers[$rand][15] ?? '',
            'sa_address_line2' => $filtered_customers[$rand][16] ?? '',
            'sa_address_line3' => $filtered_customers[$rand][17] ?? '',
            'sa_address_line4' => $filtered_customers[$rand][18] ?? '',
            'sa_city' => $filtered_customers[$rand][19] ?? '',
            'sa_region' => $filtered_customers[$rand][20] ?? '',
            'sa_zip_code' => $filtered_customers[$rand][21] ?? '',
            'sa_country' => $filtered_customers[$rand][22] ?? '',
        ];

        $first_name = $filtered_customers[$rand][3] ?? null;
        $last_name = $filtered_customers[$rand][4] ?? null;
        $email = $filtered_customers[$rand][2] ?? null;

        // foreach ($filtered_customers as $customer){
        //     $first_name = $customer[3] ?? null;
        //     $last_name = $customer[4] ?? null;
        //     $email = $customer[2] ?? null;
        //     \Log::info("'first_name='$first_name ;last_name='$last_name ;email='$email");
        // }

        // return [
        //     'full_name' => "$first_name $last_name",
        //     'email' => $email,
        // ];
    }

    function getRandomProduct(): ?array
    {
        $products = $this->csvToArray('InventoryItems-20250106.csv', ',', true);
        $filtered_products = array_filter($products, function ($customer) {
            return !empty($customer['SalesUnitPrice']) && !empty($customer['ItemName']);
        });
        return $products[array_rand($filtered_products)];
    }

    /**
     * Convert CSV file to array
     * 
     * @param string $filename Name of the CSV file in public folder
     * @param string $delimiter CSV delimiter (default: ',')
     * @param bool $includeHeaders Whether to include headers as keys (default: true)
     * @return array|null Returns array of CSV data or null if file not found
     */
    private function csvToArray(string $filename, string $delimiter = ',', bool $includeHeaders = false): ?array
    {
        $filePath = public_path($filename);

        if (!file_exists($filePath)) {
            return null;
        }

        $data = [];
        $headers = [];

        if (($handle = fopen($filePath, "r")) !== false) {
            if ($includeHeaders) {
                // Get headers and use them as keys
                $headers = fgetcsv($handle, 0, $delimiter);
                $headerCount = count($headers);
            } else {
                // Skip the header row but don't use it
                fgetcsv($handle, 0, $delimiter);
            }

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if ($includeHeaders) {
                    // Pad the row with null values if it's shorter than headers
                    $rowCount = count($row);
                    if ($rowCount < $headerCount) {
                        $row = array_pad($row, $headerCount, null);
                    }
                    // Use headers as keys
                    $rowData = array_combine($headers, $row);
                    $data[] = $rowData;
                } else {
                    // Just add the row as is
                    $data[] = $row;
                }
            }

            fclose($handle);
        }
        return $data;
    }
}
