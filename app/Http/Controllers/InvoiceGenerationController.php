<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class InvoiceGenerationController extends Controller
{
    // Declare global properties
    protected $allCustomers;
    protected $allProducts;
    protected $shuffledCustomers = [];
    protected $shuffledProducts  = [];
    protected $customerIndex     = 0;
    protected $productIndex      = 0;

    /**
     * Constructor to initialize data.
     */
    public function __construct()
    {
        // Load the customers and products once in the constructor
        $this->allCustomers = Customer::whereNotNull('first_name')->limit(request()->num_client ?? PHP_INT_MAX)->get()->toArray();
        $this->allProducts  = Product::where('unit_price', '>', 0)->get()->toArray();

        // Shuffle the customers and products initially
        $this->shuffleCustomers();
        $this->shuffleProducts();
    }

    /**
     * Get US public holidays for a given year
     */
    private function getUSHolidays($year)
    {
        $holidays = [];

                                     // Fixed date holidays
        $holidays[] = "$year-01-01"; // New Year's Day
        $holidays[] = "$year-07-04"; // Independence Day
        $holidays[] = "$year-11-11"; // Veterans Day
        $holidays[] = "$year-12-25"; // Christmas Day

        // Martin Luther King Jr. Day (3rd Monday in January)
        $holidays[] = date('Y-m-d', strtotime("third monday of january $year"));

        // Presidents Day (3rd Monday in February)
        $holidays[] = date('Y-m-d', strtotime("third monday of february $year"));

        // Memorial Day (last Monday in May)
        $holidays[] = date('Y-m-d', strtotime("last monday of may $year"));

        // Labor Day (1st Monday in September)
        $holidays[] = date('Y-m-d', strtotime("first monday of september $year"));

        // Columbus Day (2nd Monday in October)
        $holidays[] = date('Y-m-d', strtotime("second monday of october $year"));

        // Thanksgiving (4th Thursday in November)
        $thanksgiving = date('Y-m-d', strtotime("fourth thursday of november $year"));
        $holidays[]   = $thanksgiving;

        // // Black Friday (day after Thanksgiving)
        // $holidays[] = date('Y-m-d', strtotime($thanksgiving . ' +1 day'));

        return $holidays;
    }

    /**
     * Check if a date is a business day (not weekend or holiday)
     */
    private function isBusinessDay($date)
    {
        $timestamp = strtotime($date);
        $dayOfWeek = date('w', $timestamp);

        // Check if it's weekend (Saturday = 6, Sunday = 0)
        if ($dayOfWeek == 0 || $dayOfWeek == 6) {
            return false;
        }

        // Check if it's a US holiday
        $year     = date('Y', $timestamp);
        $holidays = $this->getUSHolidays($year);

        return ! in_array($date, $holidays);
    }

    /**
     * Get all business days between two dates
     */
    private function getBusinessDays($startDate, $endDate)
    {
        $businessDays = [];
        $current      = strtotime($startDate);
        $end          = strtotime($endDate);

        while ($current <= $end) {
            $currentDate = date('Y-m-d', $current);
            if ($this->isBusinessDay($currentDate)) {
                $businessDays[] = $currentDate;
            }
            $current = strtotime('+1 day', $current);
        }

        return $businessDays;
    }

    /**
     * Get the number of invoices to be generated based on total amount
     */
    public function getNoOfInvoiceToBeGenerated($totalAmount)
    {
        // You can adjust this logic based on your requirements
        // For example: 1 invoice per $1000, minimum 10, maximum 1000
        $invoicesCount = max(10, min(1000, intval($totalAmount / 1000)));
        return $invoicesCount;
    }

    public function showForm()
    {
        $startInvoiceNumber = Invoice::select(DB::raw('CAST(invoice_number AS UNSIGNED) as invoice_number'))
            ->orderBy('invoice_number', 'desc')
            ->value('invoice_number');

        $startInvoiceNumber = $startInvoiceNumber ? $startInvoiceNumber + 1 : 1;

        return view('invoices.generate', [
            'startInvoiceNumber' => $startInvoiceNumber,
        ]);
    }

    public function generateInvoices(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        DB::beginTransaction();
        try {
            // Validate user input
            $validated = $request->validate([
                'start_date'           => 'required|date',
                'end_date'             => 'required|date',
                'start_invoice_number' => 'required|integer',
                'num_invoices'         => 'nullable|integer|max:20000',
                'total_amount'         => 'required',
            ]);

            $totalInvoiceAmount                = $request->total_amount;
            $totalNumberOfInvoiceToBeGenerated = $request->num_invoices;
            $invoiceSequenceStartFrom          = $request->start_invoice_number;

            if (! $request->num_invoices) {
                $totalNumberOfInvoiceToBeGenerated = $this->getNoOfInvoiceToBeGenerated($request->total_amount);
            }

            $invoices = $this->generateInvoiceData(
                floatval($totalInvoiceAmount),
                $totalNumberOfInvoiceToBeGenerated,
                $invoiceSequenceStartFrom
            );

            $invoices = $this->assignRandomTimesAscending($invoices);

            // Delete existing ZIP files in the directory
            $zipFiles         = public_path('*.zip');
            $existingZipFiles = File::glob($zipFiles);
            foreach ($existingZipFiles as $file) {
                File::delete($file);
            }

            Cache::put('start_invoice_number', $request->start_invoice_number + count($invoices));

            // Calculate the total amount of generated invoices
            $totalGeneratedAmount = array_sum(array_column($invoices, 'total'));

            if ($request->debug == 1) {
                return response()->json([
                    'invoices'        => array_combine(array_column($invoices, 'invoice_number'), array_column($invoices, 'invoice_date')),
                    'total_generated' => $totalGeneratedAmount,
                    'target_amount'   => $totalInvoiceAmount,
                    'difference'      => $totalGeneratedAmount - $totalInvoiceAmount,
                ]);
            }

            $this->storeInvoices($invoices);
            DB::commit();
            return $this->generateInvoicesZip($invoices, "invoice_total-$totalGeneratedAmount.zip");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
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
        $items           = [];
        $remainingAmount = $targetAmount;
        $usedProducts    = [];
        $numberOfItems   = rand(1, 2);

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

            if ($isLastItem) {
                // For the last item, calculate quantity to reach the target amount
                $quantity = max(1, round($remainingAmount / $unitPrice, 2));
            } else {
                $quantity = rand(1, 4);
                // Adjust the quantity based on the unit price to ensure reasonable amounts
                while ($unitPrice * $quantity < 50 && $remainingAmount > 100) {
                    $quantity++;
                }
            }

            $amount = round($quantity * $unitPrice, 2);
            $tax    = round($amount * ($taxPercentage / 100), 2);

            $items[] = [
                'name'           => $product['product_name'],
                'description'    => $product['product_name'],
                'quantity'       => $quantity,  // Store as float, format later if needed
                'unit_price'     => $unitPrice, // Store as float, format later if needed
                'tax'            => $tax,       // Store as float, format later if needed
                'tax_percentage' => $taxPercentage,
                'amount'         => $amount, // Store as float, format later if needed
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
            return round($remainingTotal, 2);
        }

                                                    // Calculate bounds with tighter control for better accuracy
        $minAmount = max(50, $averageAmount * 0.7); // Minimum $50
        $maxAmount = min($averageAmount * 1.3, $remainingTotal - ($remainingCount - 1) * 50);

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
        $invoices             = [];
        $remainingAmount      = $totalInvoiceAmount;
        $averageInvoiceAmount = $totalInvoiceAmount / $totalNumberOfInvoiceToBeGenerated;

        // Get all business days in the date range
        $businessDays = $this->getBusinessDays(request()->start_date, request()->end_date);

        if (empty($businessDays)) {
            throw new \Exception('No business days found in the specified date range.');
        }

        // Distribute invoices across business days
        $invoicesPerDay     = [];
        $totalBusinessDays  = count($businessDays);
        $baseInvoicesPerDay = intval($totalNumberOfInvoiceToBeGenerated / $totalBusinessDays);
        $extraInvoices      = $totalNumberOfInvoiceToBeGenerated % $totalBusinessDays;

        // Distribute invoices evenly across business days
        for ($i = 0; $i < $totalBusinessDays; $i++) {
            $invoicesPerDay[$i] = $baseInvoicesPerDay;
            if ($i < $extraInvoices) {
                $invoicesPerDay[$i]++;
            }
        }

        $invoiceIndex = 0;

        // Generate invoices for each business day
        for ($dayIndex = 0; $dayIndex < $totalBusinessDays; $dayIndex++) {
            $currentDate        = $businessDays[$dayIndex];
            $invoicesForThisDay = $invoicesPerDay[$dayIndex];

            for ($i = 0; $i < $invoicesForThisDay; $i++) {
                $isLastInvoice = ($invoiceIndex == $totalNumberOfInvoiceToBeGenerated - 1);

                // Control the invoice amount deviation
                $currentInvoiceAmount = $isLastInvoice
                ? $remainingAmount
                : $this->generateRandomAmount(
                    $averageInvoiceAmount,
                    $remainingAmount,
                    $totalNumberOfInvoiceToBeGenerated - $invoiceIndex
                );

                // Ensure minimum invoice amount
                $currentInvoiceAmount = max(50, $currentInvoiceAmount);

                // Random customer and product for each invoice
                $customer      = $this->getRandomCustomer();
                $product       = $this->getRandomProduct();
                $taxPercentage = request()->tax_percentage ?? 0;

                $invoiceItems = $this->generateInvoiceItems(
                    $currentInvoiceAmount,
                    $product['product_name'],
                    $product['product_name'],
                    floatval($product['unit_price']),
                    floatval($taxPercentage)
                );

                // Now we can safely calculate totals since items store numeric values
                $subtotal    = array_sum(array_column($invoiceItems, 'amount'));
                $totalTax    = array_sum(array_column($invoiceItems, 'tax'));
                $actualTotal = $subtotal + $totalTax;

                $invoices[] = [
                     ...$customer,
                    'invoice_number' => $invoiceSequenceStartFrom + $invoiceIndex,
                    'invoice_date'   => $currentDate,
                    'invoice_items'  => $invoiceItems,
                    'subtotal'       => $subtotal,    // Store as float
                    'total_tax'      => $totalTax,    // Store as float
                    'total'          => $actualTotal, // Store as float
                ];

                // Update remaining amount
                $remainingAmount -= $actualTotal;
                $invoiceIndex++;
            }
        }

        return $invoices;
    }

    public function getRandomDate(
        int $totalNumberOfInvoices,
        int $invoiceIndex
    ): ?string {
        // This method is now replaced by the business day distribution in generateInvoiceData
        // Keeping it for backward compatibility
        $businessDays = $this->getBusinessDays(request()->start_date, request()->end_date);

        if (empty($businessDays)) {
            return null;
        }

        $randomIndex = $invoiceIndex % count($businessDays);
        return $businessDays[$randomIndex];
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
            'customer_id'      => $selectedCustomer['id'] ?? '',
            'email'            => $selectedCustomer['email'] ?? '',
            'first_name'       => $selectedCustomer['first_name'] ?? '',
            'last_name'        => $selectedCustomer['last_name'] ?? '',
            'full_name'        => "{$selectedCustomer['first_name']} {$selectedCustomer['last_name']}",
            'po_attention_to'  => "{$selectedCustomer['first_name']} {$selectedCustomer['last_name']}",
            'po_address_line1' => $selectedCustomer['po_address_line1'] ?? '',
            'po_address_line2' => $selectedCustomer['po_address_line2'] ?? '',
            'po_address_line3' => $selectedCustomer['po_address_line3'] ?? '',
            'po_address_line4' => $selectedCustomer['po_address_line4'] ?? '',
            'po_city'          => $selectedCustomer['po_city'] ?? '',
            'po_region'        => $selectedCustomer['po_region'] ?? '',
            'po_zip_code'      => $selectedCustomer['po_zip_code'] ?? '',
            'po_country'       => $selectedCustomer['po_country'] ?? '',
            'sa_attention_to'  => $selectedCustomer['sa_attention_to'] ?? '',
            'sa_address_line1' => $selectedCustomer['sa_address_line1'] ?? '',
            'sa_address_line2' => $selectedCustomer['sa_address_line2'] ?? '',
            'sa_address_line3' => $selectedCustomer['sa_address_line3'] ?? '',
            'sa_address_line4' => $selectedCustomer['sa_address_line4'] ?? '',
            'sa_city'          => $selectedCustomer['sa_city'] ?? '',
            'sa_region'        => $selectedCustomer['sa_region'] ?? '',
            'sa_zip_code'      => $selectedCustomer['sa_zip_code'] ?? '',
            'sa_country'       => $selectedCustomer['sa_country'] ?? '',
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
     * Store the invoice and its associated items.
     */
    public function storeInvoices($invoices)
    {
        // Begin database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            foreach ($invoices as $_invoices) {
                // Create the invoice
                $invoice = Invoice::create([
                    'customer_id'         => $_invoices['customer_id'],
                    'invoice_number'      => $_invoices['invoice_number'],
                    'invoice_date'        => $_invoices['invoice_date'],
                    'invoice_time'        => $_invoices['invoice_time'] ?? null,
                    'print_address_line1' => request()->print_address_line1,
                    'print_address_line2' => request()->print_address_line2,
                    'print_address_line3' => request()->print_address_line3,
                    'print_address_line4' => request()->print_address_line4,
                ]);

                // Create invoice items and associate them with the created invoice
                foreach ($_invoices['invoice_items'] as $itemData) {
                    $invoice->items()->create([
                        'name'           => $itemData['name'],
                        'description'    => $itemData['description'],
                        'quantity'       => $itemData['quantity'],
                        'unit_price'     => $itemData['unit_price'],
                        'tax'            => $itemData['tax'],
                        'tax_percentage' => $itemData['tax_percentage'],
                        'amount'         => $itemData['amount'],
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
        shuffle($this->shuffledCustomers);
        $this->customerIndex = 0;
    }

    /**
     * Shuffle the products.
     */
    protected function shuffleProducts(): void
    {
        $this->shuffledProducts = $this->allProducts;
        shuffle($this->shuffledProducts);
        $this->productIndex = 0;
    }

    public function assignRandomTimesAscending(&$invoices, $startTime = "08:00", $endTime = "16:30")
    {
        // Convert start and end times to timestamps
        $startTimestamp = strtotime($startTime);
        $endTimestamp   = strtotime($endTime);

        // Group invoices by date
        $invoicesByDate = [];
        foreach ($invoices as $key => $invoice) {
            $date = $invoice['invoice_date'];
            if (! isset($invoicesByDate[$date])) {
                $invoicesByDate[$date] = [];
            }
            $invoicesByDate[$date][] = $key;
        }

        // Assign times for each date separately
        foreach ($invoicesByDate as $date => $invoiceKeys) {
            $timeInterval = ($endTimestamp - $startTimestamp) / count($invoiceKeys);
            $currentTime  = $startTimestamp;

            foreach ($invoiceKeys as $key) {
                $invoices[$key]['invoice_time'] = date("H:i", $currentTime);
                $currentTime += $timeInterval + rand(60, 300); // Add 1-5 minutes random variation

                // Ensure we don't exceed end time
                if ($currentTime > $endTimestamp) {
                    $currentTime = $endTimestamp - rand(300, 900); // 5-15 minutes before end
                }
            }
        }

        // Sort invoices by invoice_number
        usort($invoices, function ($a, $b) {
            return $a['invoice_number'] <=> $b['invoice_number'];
        });

        return $invoices;
    }
}
