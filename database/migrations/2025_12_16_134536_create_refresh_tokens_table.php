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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            // 1. RELATION avec admin (pas email en clair)
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            $table->string('device_name')->nullable();




            $table->string("appareil");
            $table->string('email')->unique();
            $table->string('token');
            $table->integer("isValid")->default(1)->comment("0 => connection invalid or expired, 1 => connexion valid");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
