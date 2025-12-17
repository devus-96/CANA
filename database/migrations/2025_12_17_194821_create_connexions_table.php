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
        Schema::create('connexions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->timestamps();
            $table->string('device')->nullable();
            $table->string('token')->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('ville', 45)->nullable();
            $table->string('navigateur', 45)->nullable();
            $table->timestamp('expires_at');
            $table->enum('status', ['ACTIVE','REVOKED','EXPIRED','SUSPICIOUS'])->default('ACTIVE');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connexions');
    }
};
