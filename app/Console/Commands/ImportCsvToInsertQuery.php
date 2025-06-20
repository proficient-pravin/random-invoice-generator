<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportCsvToInsertQuery extends Command
{
    protected $signature   = 'import:csv-to-query {path : Path to the CSV file}';
    protected $description = 'Reads a CSV file and logs INSERT SQL queries';

    public function handle()
    {
        $path     = $this->argument('path');
        $fullPath = storage_path("app/{$path}");

        if (! file_exists($fullPath)) {
            $this->error("Excel file not found at: $fullPath");
            return;
        }

        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            $this->error("Failed to read Excel file: " . $e->getMessage());
            return;
        }

        $headers = null;
        foreach ($rows as $index => $row) {
            if (! $headers) {
                $headers = array_map('trim', $row);
                continue;
            }

            $data = array_map('trim', $row);

            if (count($headers) !== count($data)) {
                Log::warning('Skipped row due to mismatch', [
                    'headers_count' => count($headers),
                    'data_count'    => count($data),
                    'data'          => $data,
                ]);
                continue;
            }

            $rowAssoc = array_combine($headers, $data);

            $insert = [
                'email'            => null,
                'first_name'       => $rowAssoc['OWNER'] ?? '',
                'last_name'        => $rowAssoc['LAST'] ?? '',
                'po_attention_to'  => $rowAssoc['PROPOWN'] ?? null,
                'po_address_line1' => $rowAssoc['MAILADD'] ?? '',
                'po_address_line2' => trim(($rowAssoc['MAILNUM'] ?? '') . ' ' . ($rowAssoc['MAILSTR'] ?? '')),
                'po_address_line3' => null,
                'po_address_line4' => null,
                'po_city'          => $rowAssoc['MAILCITY'] ?? '',
                'po_region'        => $rowAssoc['MAILSTATE'] ?? '',
                'po_zip_code'      => $rowAssoc['MAILZIP'] ?? '',
                'po_country'       => 'USA',
                'sa_address_line1' => $rowAssoc['SITENUM'] ?? '',
                'sa_address_line2' => trim(($rowAssoc['SITESTR'] ?? '') . ' ' . ($rowAssoc['SITEAPT'] ?? '')),
                'sa_address_line3' => null,
                'sa_address_line4' => null,
                'sa_city'          => $rowAssoc['SITECITY'] ?? '',
                'sa_region'        => $rowAssoc['SITESTATE'] ?? '',
                'sa_zip_code'      => $rowAssoc['SITEZIP'] ?? '',
                'sa_country'       => 'USA',
                'created_at'       => now()->toDateTimeString(),
                'updated_at'       => now()->toDateTimeString(),
            ];

            $columns = implode(', ', array_keys($insert));
            $values  = implode(', ', array_map(
                fn($val) => is_null($val) ? 'NULL' : "'" . addslashes($val) . "'",
                array_values($insert)
            ));

            $query = "INSERT INTO customers ($columns) VALUES ($values);";

            // Output in terminal and log file
            // $this->line("Insert Array:");
            // $this->line(print_r($insert, true));

            // $this->line("Insert Query:");
            // $this->line($query);

            // Log::info("Insert Array", $insert);
            Log::info("Insert Query: " . $query);
        }

        $this->info("Finished processing Excel file.");
    }
}
