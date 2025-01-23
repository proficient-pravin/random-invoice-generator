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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('print_address_line4')->nullable()->after('invoice_date');
            $table->string('print_address_line3')->nullable()->after('invoice_date');
            $table->string('print_address_line2')->nullable()->after('invoice_date');
            $table->string('print_address_line1')->nullable()->after('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'print_address_line4',
                'print_address_line3',
                'print_address_line2',
                'print_address_line1',
            ]);
        });
    }
};
