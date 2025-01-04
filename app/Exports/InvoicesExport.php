<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoicesExport implements FromCollection, WithHeadings
{
    protected $invoicesData;

    public function __construct($invoicesData)
    {
        $this->invoicesData = $invoicesData;
    }

    public function collection()
    {
        return collect($this->invoicesData);
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Product Name',
            'Product Price',
            'Quantity',
            'Amount',
            'Tax',
            'Total',
            'Invoice Date',
        ];
    }
}
