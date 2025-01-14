<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    // In the Invoice model
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the customer that owns the invoice.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
