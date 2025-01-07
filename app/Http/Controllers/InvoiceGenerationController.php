<?php

namespace App\Http\Controllers;

// use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\File;


class InvoiceGenerationController extends Controller
{
    public function showForm()
    {
        // $pdf = Pdf::loadView('invoice_template_final2');
        // return $pdf->stream('invoice-' . time() . '.pdf');
        return view('invoice_form');
    }

    public function generateInvoices(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1'); // Unlimited memory

        // Validate user input
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'start_invoice_number' => 'required|integer',
            'num_invoices' => 'nullable|integer|max:150',
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

        if(empty(request()->num_invoices)){
            $invoices = $this->generateInvoiceDataV2(
                floatval($totalInvoiceAmount),
                $totalNumberOfInvoiceToBeGenerated,
                $invoiceSequenceStartFrom
            );
        }else{
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

        // Calculate the total amount of generated invoices
        $totalGeneratedAmount = array_sum(array_column($invoices, 'total'));

        return $this->generateInvoicesZip($invoices, "invoice_total-$totalGeneratedAmount.zip");
        // dd($invoices, $totalGeneratedAmount);
        
        // $invoice = collect($invoices[0]);
        // dd($invoice);

        // // Pass data to the Blade view for PDF generation
        // $pdf = Pdf::loadView('invoice_template_final', compact('invoice'));

        // return $pdf->stream('invoice-' . time() . '.pdf');

        // // Return the PDF to the browser
        // return $pdf->download('invoice-' . time() . '.pdf');
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
        $totalQuantity = request()->total_amount  > 40000 ? 3 : 4;
        $numberOfItems = rand(1, $totalQuantity);
    
        for ($i = 0; $i < $numberOfItems; $i++) {
            $isLastItem = ($i == $numberOfItems - 1);
    
            do {
                $product = $this->getRandomProduct();
            } while (
                in_array($product['ItemName'], $usedProducts) ||
                floatval($product['SalesUnitPrice']) <= 0
            );
    
            $usedProducts[] = $product['ItemName'];
    
            $unitPrice = floatval($product['SalesUnitPrice']);
            $quantity = $isLastItem
                ? max(1, min(4, ceil($remainingAmount / $unitPrice)))
                : rand(1, 4);
    
            $amount = round($quantity * $unitPrice, 2);
            $tax = round($amount * ($taxPercentage / 100), 2);
    
            $items[] = [
                'name' => $product['ItemName'],
                'description' => $product['PurchasesDescription'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax' => $tax,
                'amount' => $amount,
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

    private function generateInvoiceDataV2(
        float $totalInvoiceAmount,
        int $invoiceSequenceStartFrom
    ): array {
        $invoices = [];
        $remainingAmount = $totalInvoiceAmount;
        $totalGeneratedAmount = 0; // Track the total generated amount
    
        while ($remainingAmount > 0) {
            // Random customer and product for each invoice
            $customer = $this->getRandomCustomer();
            $product = $this->getRandomProduct();
            $taxPercentage = $product['SalesTaxRate'] == 'Tax on Sales' ? (request()->tax_percentage ?? 10) : 0;
    
            // Generate invoice items
            $invoiceItems = $this->generateInvoiceItems(
                $remainingAmount,
                $product['ItemName'],
                $product['PurchasesDescription'],
                floatval($product['SalesUnitPrice']),
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
                'invoice_date' => $this->getRandomDate(),
                'invoice_items' => $invoiceItems,
                'subtotal' => round($subtotal, 2),
                'total_tax' => round($totalTax, 2),
                'total' => round($currentInvoiceAmount, 2),
            ];
        }
    
        return $invoices;
    }

    public function getRandomDate()
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

    public function getRandomCustomer(): ?array
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

    public function getRandomProduct(): ?array
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
            foreach ($invoices as $index => $invoice) {
                // Generate PDF for the invoice
                $pdf = PDF::loadView('invoice_template_final', ['invoice' => $invoice]);
                $pdfPath = 'invoice_' . ($index + 1) . '/pdf_invoice_' . ($index + 1) . '.pdf';

                // Add the PDF to the ZIP
                $zip->addFromString($pdfPath, $pdf->output());

                // Generate CSV for the invoice
                $csvData = $this->generateInvoiceCsv($invoice);
                $csvPath = 'invoice_' . ($index + 1) . '/csv_invoice_' . ($index + 1) . '.csv';
                // Add the CSV to the ZIP
                $zip->addFromString($csvPath, $csvData);
            }

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
    private function generateInvoiceCsv(array $invoice): string
    {
        $csvHeader = [
            'ContactName', 'EmailAddress', 'POAddressLine1', 'POAddressLine2', 'POAddressLine3', 'POAddressLine4',
            'POCity', 'PORegion', 'POPostalCode', 'POCountry', 'InvoiceNumber', 'Reference', 'InvoiceDate',
            'DueDate', 'Description', 'Quantity', 'UnitAmount', 'Discount', 'TaxAmount', 'Total',
        ];

        $csvRows = [];

        foreach ($invoice['invoice_items'] as $item) {
            $csvRows[] = [
                '702 Print & Marketing LLC',
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

    // private function generateInvoiceData(
    //     float $totalInvoiceAmount,
    //     int $totalNumberOfInvoiceToBeGenerated,
    //     int $invoiceSequenceStartFrom
    // ): array {
    //     $invoices = [];
    //     $remainingAmount = $totalInvoiceAmount;
    //     $averageInvoiceAmount = $totalInvoiceAmount / $totalNumberOfInvoiceToBeGenerated;

    //     // Generate invoices with small variations to make the total amount close to the requested amount
    //     for ($i = 0; $i < $totalNumberOfInvoiceToBeGenerated; $i++) {
    //         $isLastInvoice = ($i == $totalNumberOfInvoiceToBeGenerated - 1);

    //         // Control the invoice amount deviation for all invoices except the last one
    //         $currentInvoiceAmount = ($isLastInvoice)
    //         ? $remainingAmount
    //         : $this->generateRandomAmount($averageInvoiceAmount, $remainingAmount, $totalNumberOfInvoiceToBeGenerated - $i);

    //         // Random customer and product for each invoice
    //         $customer = $this->getRandomCustomer();
    //         $product = $this->getRandomProduct();
    //         $taxPercentage = $product['SalesTaxRate'] == 'Tax on Sales' ? (request()->tax_percentage ?? 10) : 0;

    //         $invoiceItems = $this->generateInvoiceItems(
    //             $currentInvoiceAmount,
    //             $product['ItemName'],
    //             $product['PurchasesDescription'],
    //             floatval($product['SalesUnitPrice']),
    //             floatval($taxPercentage)
    //         );

    //         $subtotal = array_sum(array_column($invoiceItems, 'amount'));
    //         $totalTax = array_sum(array_column($invoiceItems, 'tax'));

    //         $invoices[] = [
    //              ...$customer,
    //             'invoice_number' => $invoiceSequenceStartFrom + $i,
    //             'invoice_date' => $this->getRandomDate(),
    //             'invoice_items' => $invoiceItems,
    //             'subtotal' => round($subtotal, 2),
    //             'total_tax' => round($totalTax, 2),
    //             'total' => round($subtotal + $totalTax, 2),
    //         ];

    //         // Update remaining amount
    //         $remainingAmount -= $currentInvoiceAmount;
    //     }

    //     return $invoices;
    // }

}
