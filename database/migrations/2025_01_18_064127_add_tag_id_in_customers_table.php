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
        Schema::table('customers', function (Blueprint $table) {
             // Add the tag_id column with foreign key constraint
             $table->unsignedBigInteger('tag_id')->nullable()->after('id'); // After the 'id' column, or wherever you prefer

             // Add foreign key constraint (assuming 'tags' table exists and has an 'id' column)
             $table->foreign('tag_id')->references('id')->on('tags')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop the foreign key and column if rolling back the migration
            $table->dropForeign(['tag_id']);
            $table->dropColumn('tag_id');
        });
    }
};
