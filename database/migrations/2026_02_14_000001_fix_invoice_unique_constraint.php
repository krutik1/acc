<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Debugging: show indexes
        // We will try to find the index and drop it by name using raw SQL
        $indexes = DB::select("SHOW INDEXES FROM invoices");

        foreach ($indexes as $index) {
            // Check for index name 'invoices_invoice_number_unique'
            if ($index->Key_name === 'invoices_invoice_number_unique') {
                try {
                    // Try to drop it using Schema builder if available, or fallback to raw
                    Schema::table('invoices', function (Blueprint $table) {
                        $table->dropUnique('invoices_invoice_number_unique');
                    });
                } catch (\Exception $e) {
                    // Fallback to raw SQL if Schema builder fails
                    try {
                        DB::statement("ALTER TABLE invoices DROP INDEX invoices_invoice_number_unique");
                    } catch (\Exception $e2) {
                        // ignore
                    }
                }
                break; // Found and attempted drop
            }
        }

        // Also check if index 'invoice_number' exists (sometimes auto-generated differently)
        foreach ($indexes as $index) {
            if ($index->Key_name === 'invoice_number' && $index->Non_unique == 0) {
                try {
                    DB::statement("ALTER TABLE invoices DROP INDEX invoice_number");
                } catch (\Exception $e) {
                }
            }
        }

        // Add new unique index
        // Check if new index already exists
        $newIndexExists = false;
        $indexesAfter = DB::select("SHOW INDEXES FROM invoices");
        foreach ($indexesAfter as $index) {
            if ($index->Key_name === 'invoices_company_invoice_unique') {
                $newIndexExists = true;
                break;
            }
        }

        if (!$newIndexExists) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unique(['company_id', 'invoice_number'], 'invoices_company_invoice_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Try to revert
        try {
            DB::statement("ALTER TABLE invoices DROP INDEX invoices_company_invoice_unique");
        } catch (\Exception $e) {
        }

        try {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unique('invoice_number', 'invoices_invoice_number_unique');
            });
        } catch (\Exception $e) {
        }
    }
};
