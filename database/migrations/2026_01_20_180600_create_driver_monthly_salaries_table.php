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
        Schema::create('driver_monthly_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('month'); // YYYY-MM
            $table->integer('total_trips')->default(0);
            $table->decimal('total_quantity', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0); // Sum of trips commission
            $table->decimal('total_upaad', 10, 2)->default(0); // Deducted advances
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deduction', 10, 2)->default(0); // Other deductions
            $table->decimal('payable_amount', 10, 2)->default(0);
            $table->string('status')->default('generated'); // generated, paid
            $table->date('payment_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_monthly_salaries');
    }
};
