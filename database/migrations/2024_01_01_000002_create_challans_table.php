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
        Schema::create('challans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained('parties')->onDelete('cascade');
            $table->string('challan_number', 50)->unique();
            $table->date('challan_date');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->boolean('is_invoiced')->default(false);
            $table->timestamps();
            
            $table->index('party_id');
            $table->index('challan_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challans');
    }
};
