<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InvoiceGenerationController extends Controller
{
    protected $allCustomers;
    protected $allProducts;
    protected $shuffledCustomers = [];
    protected $shuffledProducts  = [];
    protected $customerIndex     = 0;
    protected $productIndex      = 0;

    public function __construct()
    {
        $this->allCustomers = Customer::whereNotNull('first_name')->get()->toArray();
        $this->allProducts  = Product::where('unit_price', '>', 0)->get()->toArray();
        $this->shuffleCustomers();
        $this->shuffleProducts();
    }

    public function showForm()
    {
        $startInvoiceNumber = Invoice::select(DB::raw('CAST(invoice_number AS UNSIGNED) as invoice_number'))
            ->orderBy('invoice_number', 'desc')
            ->value('invoice_number');

        return view('invoices.generate', [
            'startInvoiceNumber' => $startInvoiceNumber ? $startInvoiceNumber + 1 : 1,
        ]);
    }

    public function generateInvoices(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $validated = $request->validate([
            'start_date'           => 'required|date',
            'end_date'             => 'required|date',
            'start_invoice_number' => 'required|integer',
            'total_amount'         => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            $businessDays = $this->getBusinessDays($request->start_date, $request->end_date);
            if (count($businessDays) === 0) {
                throw new \Exception("No business days found.");
            }

            $totalAmount    = (float) $request->total_amount;
            $invoiceCount   = intval($totalAmount / rand(800, 1200));
            $invoicesPerDay = $this->distributeInvoicesPerDay($invoiceCount, $businessDays);
            $amounts        = $this->generateInvoiceAmounts($totalAmount, $invoiceCount);

            $invoices     = [];
            $invoiceIndex = 0;

            foreach ($invoicesPerDay as $i => $count) {
                $date = $businessDays[$i];
                for ($j = 0; $j < $count; $j++) {
                    $amount   = $amounts[$invoiceIndex];
                    $customer = $this->getRandomCustomer();
                    $product  = $this->getRandomProduct();

                    $items = $this->generateInvoiceItems(
                        $amount,
                        $product['product_name'],
                        $product['product_name'],
                        $product['unit_price'],
                        $request->tax_percentage ?? 0
                    );

                    $subtotal = array_sum(array_column($items, 'amount'));
                    $tax      = array_sum(array_column($items, 'tax'));

                    $invoices[] = array_merge($customer, [
                        'invoice_number' => $request->start_invoice_number + $invoiceIndex,
                        'invoice_date'   => $date,
                        'invoice_items'  => $items,
                        'subtotal'       => $subtotal,
                        'total_tax'      => $tax,
                        'total'          => $subtotal + $tax,
                    ]);

                    $invoiceIndex++;
                }
            }

            $invoices = $this->assignRandomTimesAscending($invoices);
            $this->storeInvoices($invoices);

            Cache::put('start_invoice_number', $request->start_invoice_number + count($invoices));
            DB::commit();

            return $this->generateInvoicesZip($invoices, "invoice_total-" . number_format(array_sum(array_column($invoices, 'total')), 2, '.', '') . ".zip");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    private function getBusinessDays($startDate, $endDate)
    {
        $holidays = $this->getUSHolidays(date('Y'));
        $days     = [];
        $current  = strtotime($startDate);
        $end      = strtotime($endDate);

        while ($current <= $end) {
            $day     = date('Y-m-d', $current);
            $weekday = date('w', $current);
            if (! in_array($day, $holidays) && $weekday != 0 && $weekday != 6) {
                $days[] = $day;
            }
            $current = strtotime('+1 day', $current);
        }

        return $days;
    }

    private function getUSHolidays($year)
    {
        return [
            "$year-01-01", "$year-07-04", "$year-11-11", "$year-12-25",
            date('Y-m-d', strtotime("third monday of january $year")),
            date('Y-m-d', strtotime("third monday of february $year")),
            date('Y-m-d', strtotime("last monday of may $year")),
            date('Y-m-d', strtotime("first monday of september $year")),
            date('Y-m-d', strtotime("second monday of october $year")),
            date('Y-m-d', strtotime("fourth thursday of november $year")),
        ];
    }

    private function distributeInvoicesPerDay($totalInvoices, $businessDays)
    {
        $min       = 5;
        $max       = 15;
        $totalDays = count($businessDays);
        $result    = array_fill(0, $totalDays, 0);

        while ($totalInvoices > 0) {
            for ($i = 0; $i < $totalDays; $i++) {
                if ($result[$i] < $max && $totalInvoices > 0) {
                    $canAdd = min($max - $result[$i], $totalInvoices);
                    $toAdd  = rand(0, $canAdd);
                    $result[$i] += $toAdd;
                    $totalInvoices -= $toAdd;
                }
            }
        }

        return $result;
    }

    private function generateInvoiceAmounts($targetAmount, $count)
    {
        $amounts   = [];
        $remaining = $targetAmount;

        for ($i = 0; $i < $count; $i++) {
            $remainingCount = $count - $i;
            if ($remainingCount == 1) {
                $amounts[] = round($remaining, 2);
            } else {
                $avg       = $remaining / $remainingCount;
                $min       = max(50, $avg * 0.8);
                $max       = $avg * 1.2;
                $value     = round(mt_rand($min * 100, $max * 100) / 100, 2);
                $value     = min($value, $remaining - ($remainingCount - 1) * 50);
                $amounts[] = $value;
                $remaining -= $value;
            }
        }

        return $amounts;
    }

    private function generateInvoiceItems($amount, $name, $desc, $unitPrice, $taxPercent)
    {
        $quantity = max(1, round($amount / $unitPrice));
        $subtotal = round($unitPrice * $quantity, 2);
        $tax      = round($subtotal * $taxPercent / 100, 2);

        return [[
            'name'           => $name,
            'description'    => $desc,
            'quantity'       => $quantity,
            'unit_price'     => $unitPrice,
            'tax'            => $tax,
            'tax_percentage' => $taxPercent,
            'amount'         => $subtotal,
        ]];
    }

    private function getRandomCustomer(): array
    {
        if ($this->customerIndex >= count($this->shuffledCustomers)) {
            $this->shuffleCustomers();
        }
        return $this->shuffledCustomers[$this->customerIndex++];
    }

    private function getRandomProduct(): array
    {
        if ($this->productIndex >= count($this->shuffledProducts)) {
            $this->shuffleProducts();
        }
        return $this->shuffledProducts[$this->productIndex++];
    }

    private function shuffleCustomers()
    {
        $this->shuffledCustomers = $this->allCustomers;
        shuffle($this->shuffledCustomers);
        $this->customerIndex = 0;
    }

    private function shuffleProducts()
    {
        $this->shuffledProducts = $this->allProducts;
        shuffle($this->shuffledProducts);
        $this->productIndex = 0;
    }

    private function assignRandomTimesAscending(&$invoices, $startTime = "08:00", $endTime = "16:30")
    {
        $startTimestamp = strtotime($startTime);
        $endTimestamp   = strtotime($endTime);

        $grouped = [];
        foreach ($invoices as $key => $invoice) {
            $grouped[$invoice['invoice_date']][] = $key;
        }

        foreach ($grouped as $date => $keys) {
            $step    = ($endTimestamp - $startTimestamp) / count($keys);
            $current = $startTimestamp;
            foreach ($keys as $k) {
                $invoices[$k]['invoice_time'] = date("H:i", $current);
                $current += $step + rand(60, 300);
                if ($current > $endTimestamp) {
                    $current = $endTimestamp - rand(60, 300);
                }

            }
        }

        return $invoices;
    }

    private function storeInvoices($invoices)
    {
        foreach ($invoices as $data) {
            $invoice = Invoice::create([
                'customer_id'    => $data['id'],
                'invoice_number' => $data['invoice_number'],
                'invoice_date'   => $data['invoice_date'],
                'invoice_time'   => $data['invoice_time'] ?? null,
            ]);

            foreach ($data['invoice_items'] as $item) {
                $invoice->items()->create($item);
            }
        }
    }

}
