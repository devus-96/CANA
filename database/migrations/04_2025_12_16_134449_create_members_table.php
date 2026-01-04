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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('gender', ['male', 'female']);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('member_image')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('city')->nullable();
            $table->string('parish')->nullable();
            // verification de l'admin
            $table->boolean('is_verified')->default(false);
            // Clés étrangères
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('restrict');
            $table->foreignId('stateOfLive_id')->nullable()->constrained('state_of_lives')->onDelete('set null');
            // remember token for "remember me" functionality
            $table->rememberToken();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menbers');
    }
};
