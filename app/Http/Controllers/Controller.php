<?php
namespace App\Http\Controllers;

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

}
