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
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('admin_image')->nullable();
            $table->text('biography')->nullable();
            $table->string('parish')->nullable();
            $table->string('fonction');
            // statut de l'admin
            $table->enum('status', ['ACTIVE', 'PENDING', 'REJECTED', 'BLOCKED'])->default('PENDING');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            // verification de l'admin
            $table->boolean('is_verified')->default(false);
            // remember token for "remember me" functionality
            $table->rememberToken();
            $table->softDeletes('deleted_at', precision: 0);
            // Clés étrangères
            $table->foreignId('assigned_by')->nullable()->constrained('admins')->onDelete('set null'); // admin qui l'a attribuer son role
            $table->foreignId('status_updated_by')->nullable()->constrained('admins')->onDelete('set null'); // admin qui l'a attribuer son status

            $table->timestamps();
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
