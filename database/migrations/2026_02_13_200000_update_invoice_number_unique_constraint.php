<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the existing unique constraint on invoice_number
            $table->dropUnique('invoices_invoice_number_unique');

            // Add a new composite unique constraint on company_id and invoice_number
            $table->unique(['company_id', 'invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['company_id', 'invoice_number']);

            // Restore the unique constraint on invoice_number
            $table->unique(['invoice_number']);
        });
    }
};
