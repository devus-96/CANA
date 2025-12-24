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
            $table->foreignId('author_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->integer('views_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->timestamps();
            $table->softDeletes('deleted_at', precision: 0);

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
