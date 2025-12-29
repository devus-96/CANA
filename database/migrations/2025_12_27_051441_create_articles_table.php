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
        Schema::create('articles', function (Blueprint $table) {
                $table->id(); // id (primary key)
                $table->unsignedBigInteger('category_id'); // categoryId
                $table->unsignedBigInteger('author_id'); // authorId
                $table->string('title'); // title
                $table->string('article_image')->nullable(); // image (nullable in case an article may not have an image)
                $table->text('content'); // content (text type)
                $table->string('slug')->unique(); // slug (unique)
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft'); // status with default value 'draft'
                // Statistiques
                $table->unsignedInteger('share_count')->default(0);
                $table->unsignedInteger('views_count')->default(0);
                // Foreign keys (if categories and authors tables exist)
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->foreign('author_id')->references('id')->on('admins')->onDelete('cascade');

                $table->softDeletes();
                $table->timestamps(); // Laravel's created_at and updated_at (optional, but recommended)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
