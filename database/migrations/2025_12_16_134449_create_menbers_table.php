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
        Schema::create('menbers', function (Blueprint $table) {
            $table->id();
            $table->integer('stateOfLive_id')->unsigned()->nullable();
            $table->integer('role_id')->unsigned();
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female']);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('profile_photo_url')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('city')->nullable();
            $table->string('parish')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->rememberToken();
            $table->softDeletes('deleted_at', precision: 0);
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
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
