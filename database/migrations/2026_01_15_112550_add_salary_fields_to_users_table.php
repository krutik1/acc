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
            // Modify existing payment_mode column to include 'fixed'
            $table->string('payment_mode')->default('trip')->change(); 
            // Note: DB::statement might be needed for ENUM change depending on DB driver, 
            // but for simplicity we will switch to string or raw statement if needed. 
            // Given Laravel limitations with ENUM changes, often it's better to just ensure application level validation 
            // or use raw SQL. For now, assuming 'string' or purely adding 'fixed_salary'

            $table->decimal('fixed_salary', 10, 2)->default(0)->after('payment_mode');
        });
        
        // Since changing ENUM is tricky, we'll try raw statement if we want to keep it DB-level strict, 
        // OR we just rely on application level and maybe change column type to string if it was enum.
        // Let's check the previous migration "2026_01_13_183300" that added 'payment_mode' as ENUM.
        
        DB::statement("ALTER TABLE users MODIFY COLUMN payment_mode ENUM('trip', 'pcs', 'fixed') DEFAULT 'trip'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fixed_salary']);
        });
        
        // Revert enum
        DB::statement("ALTER TABLE users MODIFY COLUMN payment_mode ENUM('trip', 'pcs') DEFAULT 'trip'");
    }
};
