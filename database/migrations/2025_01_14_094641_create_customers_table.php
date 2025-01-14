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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('last_name');

            $table->string('po_attention_to')->nullable();
            $table->string('po_address_line1');
            $table->string('po_address_line2')->nullable();
            $table->string('po_address_line3')->nullable();
            $table->string('po_address_line4')->nullable();
            $table->string('po_city');
            $table->string('po_region')->nullable();
            $table->string('po_zip_code');
            $table->string('po_country');

            $table->string('sa_address_line1');
            $table->string('sa_address_line2')->nullable();
            $table->string('sa_address_line3')->nullable();
            $table->string('sa_address_line4')->nullable();
            $table->string('sa_city');
            $table->string('sa_region')->nullable();
            $table->string('sa_zip_code');
            $table->string('sa_country');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
