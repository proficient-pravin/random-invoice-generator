<?php
namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use ZipArchive;

abstract class Controller
{
    /**
     * Determine the number of invoices to be generated based on the total amount.
     *
     * @param float $totalAmount
     * @return int
     */
    function getNoOfInvoiceToBeGenerated($totalAmount)
    {
        // Define the minimum and maximum invoice amounts
        $avg = 250;

        // Calculate the number of invoices required
        $invoices = (int) ceil($totalAmount / $avg);

        // Adjust the invoice count if splitting is not precise
        return $invoices;
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
        $zip     = new ZipArchive;
        $zipPath = public_path($zipFileName);

        // Open the ZIP file for writing
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $htmlContent = '';
            foreach ($invoices as $index => $invoice) {
                $invoice['print_address_line1'] = $invoice['print_address_line1'] ?? request()->print_address_line1 ?? null;
                $invoice['print_address_line2'] = $invoice['print_address_line2'] ?? request()->print_address_line2 ?? null;
                $invoice['print_address_line3'] = $invoice['print_address_line3'] ?? request()->print_address_line3 ?? null;
                $invoice['print_address_line4'] = $invoice['print_address_line4'] ?? request()->print_address_line4 ?? null;

                if (in_array('single_pdf', request()->output_type ?? [])) {
                    // Generate PDF for the invoice
                    $name    = "[{$invoice['invoice_number']}]_{$invoice['full_name']}_{$invoice['invoice_date']}";
                    $pdf     = PDF::loadView('invoice_template_final', ['invoice' => $invoice]);
                    $pdfPath = "pdf_{$name}" . '.pdf';

                    // Add the PDF to the ZIP
                    $zip->addFromString($pdfPath, $pdf->output());
                }

                if (in_array('all_pdfs', request()->output_type ?? [])) {
                    $htmlContent .= view('invoice_template_final', ['invoice' => $invoice])->render();
                }
                // // Add a page break after each invoice (if needed)
                // $htmlContent .= '<div style="page-break-after: always;"></div>';

            }

            if (in_array('all_pdfs', request()->output_type ?? [])) {
                // Generate a single PDF from the combined HTML
                $pdf = PDF::loadHTML($htmlContent);

                // Add the single PDF to the ZIP
                $pdfPath = 'all_invoices.pdf';
                $zip->addFromString($pdfPath, $pdf->output());

            }

            // Generate CSV for the invoice
            $csvData = $this->generateInvoiceCsv($invoices);

            if (in_array('csv', request()->output_type ?? []) || empty(request()->output_type)) {
                $csvPath = 'csv_invoice.csv';
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
                $lastName  = $invoice['last_name'];
                $fullName  = $firstName . ' ' . $lastName;
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

}
