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
            $table->integer('admin_id');
            $table->string('device')->nullable();
            $table->string('token')->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('city', 45)->nullable();
            $table->string('navigator', 45)->nullable();
            $table->timestamp('expired_at');
            $table->enum('status', ['ACTIVE','REVOKED','EXPIRED','SUSPICIOUS'])->default('ACTIVE');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
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
