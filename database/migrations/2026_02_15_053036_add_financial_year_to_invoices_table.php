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
            $table->string('financial_year')->nullable()->after('invoice_date');
        });

        // Populate financial_year for existing records
        $invoices = \Illuminate\Support\Facades\DB::table('invoices')->get();
        foreach ($invoices as $invoice) {
            $date = \Carbon\Carbon::parse($invoice->invoice_date);
            $year = $date->year;
            // FY starts April 1st.
            // If month is Jan(1), Feb(2), Mar(3), then FY started in previous year.
            if ($date->month < 4) {
                $financialYear = ($year - 1) . '-' . $year;
            } else {
                $financialYear = $year . '-' . ($year + 1);
            }

            \Illuminate\Support\Facades\DB::table('invoices')
                ->where('id', $invoice->id)
                ->update(['financial_year' => $financialYear]);
        }

        // Now add the unique constraint
        Schema::table('invoices', function (Blueprint $table) {
            // Drop existing unique index if it exists (check first or assume it doesn't based on previous analysis)
            // We saw `id` is unique, `invoice_number` was NOT unique in previous `model:show` output.

            $table->unique(['company_id', 'invoice_number', 'financial_year'], 'invoices_comp_inv_fy_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_comp_inv_fy_unique');
            $table->dropColumn('financial_year');
        });
    }
};
