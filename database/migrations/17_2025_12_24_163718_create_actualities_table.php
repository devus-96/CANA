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
        Schema::create('actualities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('actuality_image')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
           // Statistiques
            $table->unsignedInteger('share_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            // Foreign keys
            $table->foreignId('activity_id')->nullable()->constrained('activities')->onDelete('set null');
            $table->foreignId('author_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            // Timestamps and soft deletes
            $table->timestamps();

            $table->index('author_id');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualities');
    }
};
