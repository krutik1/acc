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
        Schema::table('challans', function (Blueprint $table) {
            $table->string('financial_year')->nullable()->after('challan_date');
        });

        // Populate financial_year for existing records
        $challans = \Illuminate\Support\Facades\DB::table('challans')->get();
        foreach ($challans as $challan) {
            $date = \Carbon\Carbon::parse($challan->challan_date);
            $year = $date->year;
            // FY starts April 1st.
            if ($date->month < 4) {
                $financialYear = ($year - 1) . '-' . $year;
            } else {
                $financialYear = $year . '-' . ($year + 1);
            }

            \Illuminate\Support\Facades\DB::table('challans')
                ->where('id', $challan->id)
                ->update(['financial_year' => $financialYear]);
        }

        Schema::table('challans', function (Blueprint $table) {
            // Drop existing unique index
            $table->dropUnique('challans_challan_number_unique');

            // Add new composite unique index
            // unique(['company_id', 'party_id', 'challan_number', 'financial_year'], 'challans_comp_party_num_fy_unique')
            $table->unique(['company_id', 'party_id', 'challan_number', 'financial_year'], 'challans_comp_party_num_fy_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challans', function (Blueprint $table) {
            $table->dropUnique('challans_comp_party_num_fy_unique');
            $table->unique('challan_number', 'challans_challan_number_unique');
            $table->dropColumn('financial_year');
        });
    }
};
