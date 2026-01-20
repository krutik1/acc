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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('payment_mode', ['trip', 'pcs'])->default('trip')->after('company_id');
            $table->decimal('trip_rate', 10, 2)->default(0)->after('payment_mode');
            $table->decimal('pcs_rate', 10, 2)->default(0)->after('trip_rate');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->enum('payment_mode', ['trip', 'pcs'])->nullable()->after('status');
            $table->decimal('trip_rate', 10, 2)->nullable()->after('payment_mode');
            $table->decimal('pcs_rate', 10, 2)->nullable()->after('trip_rate');
            $table->decimal('driver_commission', 10, 2)->default(0)->after('pcs_rate');
            $table->integer('pcs')->default(0)->after('driver_commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payment_mode', 'trip_rate', 'pcs_rate']);
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['payment_mode', 'trip_rate', 'pcs_rate', 'driver_commission', 'pcs']);
        });
    }
};
