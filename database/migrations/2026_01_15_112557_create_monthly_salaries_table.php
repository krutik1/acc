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
        Schema::create('monthly_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('month'); // Format: YYYY-MM
            $table->enum('salary_type', ['fixed', 'piece']);
            $table->decimal('fixed_salary', 10, 2)->default(0); // Snapshot
            $table->decimal('piece_rate', 10, 2)->default(0); // Snapshot
            $table->integer('total_pieces')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0); // Gross Earnings
            $table->decimal('total_upaad', 12, 2)->default(0); // Deductions
            $table->decimal('payable_amount', 12, 2)->default(0); // Net Payable
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate salary generation for same user & month
            $table->unique(['user_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_salaries');
    }
};
