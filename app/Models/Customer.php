<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

     /**
     * Get the customer that belogs the tag.
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
