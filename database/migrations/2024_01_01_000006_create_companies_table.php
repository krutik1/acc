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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->string('gst_number', 20)->nullable();
            $table->string('state_code', 10)->nullable();
            $table->string('mobile_numbers')->nullable(); // Comma-separated mobile numbers
            $table->string('bank_name')->nullable();
            $table->string('ifsc_code', 20)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->text('terms_conditions')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
