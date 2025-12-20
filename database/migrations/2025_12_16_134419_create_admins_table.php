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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manager_id')->nullable()->default(null);
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('profile_photo_url')->nullable();
            $table->text('biography')->nullable();
            $table->string('parish')->nullable();
            $table->enum('status', ['ACTIVE', 'PENDING', 'REJECTED', 'BLOCKED'])->default('PENDING');
            $table->timestamp('activated_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->rememberToken();
            $table->softDeletes('deleted_at', precision: 0);
            $table->timestamps();

            $table->foreign('manager_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
