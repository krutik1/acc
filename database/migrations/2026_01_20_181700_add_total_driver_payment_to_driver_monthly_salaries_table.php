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
            $table->decimal('total_driver_payment', 10, 2)->default(0)->after('total_upaad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_monthly_salaries', function (Blueprint $table) {
            $table->dropColumn('total_driver_payment');
        });
    }
};
