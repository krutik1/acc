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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->enum('type', ['credit', 'debit']); // Credit = Income, Debit = Expense
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('category')->nullable(); // For reporting: Sales, Purchase, Salary, etc.

            // For future linking to other modules
            $table->nullableMorphs('related');

            $table->timestamps();

            // Index for faster queries
            $table->index(['bank_account_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
