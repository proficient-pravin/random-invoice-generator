<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade'); // Foreign key to invoices table
            $table->string('name'); // Name of the product/service
            $table->text('description')->nullable(); // Description of the product/service
            $table->decimal('quantity', 8, 2); // Quantity of the product/service
            $table->decimal('unit_price', 10, 2); // Unit price of the product/service
            $table->decimal('tax_percentage', 10, 2)->nullable(); // Tax applied to the product/service
            $table->decimal('tax', 10, 2)->nullable(); // Tax applied to the product/service
            $table->decimal('amount', 10, 2); // Total amount for this item (including tax)

            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
