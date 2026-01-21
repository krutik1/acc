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
            $table->decimal('fixed_trip_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('pcs_trip_amount', 10, 2)->default(0)->after('fixed_trip_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_monthly_salaries', function (Blueprint $table) {
            $table->dropColumn(['fixed_trip_amount', 'pcs_trip_amount']);
        });
    }
};
