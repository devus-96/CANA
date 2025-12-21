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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained();
            $table->tinyInteger('method');
            $table->decimal('amount', 10, 2);
            $table->string('ticket_type');
            $table->string('transaction_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('phone_number');
            $table->timestamps(); // Ajoute created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
