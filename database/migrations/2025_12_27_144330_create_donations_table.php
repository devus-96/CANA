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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('name'); // ou séparer en first_name/last_name
            $table->string('email');
            $table->string('phone')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->text('dedication')->nullable();
            // payment
            $table->tinyInteger('method');
            $table->decimal('amount', 10, 2);
            $table->string('transaction_id')->nullable();
            $table->tinyInteger('status'); // 0=pending,1=completed,2=failed.
            // Cles etrangeres
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
