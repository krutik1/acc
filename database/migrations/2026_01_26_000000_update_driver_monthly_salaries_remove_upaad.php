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
        Schema::table('driver_monthly_salaries', function (Blueprint $table) {
            // Drop total_upaad column
            if (Schema::hasColumn('driver_monthly_salaries', 'total_upaad')) {
                $table->dropColumn('total_upaad');
            }
            
            // Rename total_driver_payment to advance_amount if it exists
            if (Schema::hasColumn('driver_monthly_salaries', 'total_driver_payment')) {
                $table->renameColumn('total_driver_payment', 'advance_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_monthly_salaries', function (Blueprint $table) {
            $table->decimal('total_upaad', 10, 2)->default(0)->after('total_amount');
            
            if (Schema::hasColumn('driver_monthly_salaries', 'advance_amount')) {
                $table->renameColumn('advance_amount', 'total_driver_payment');
            }
        });
    }
};
