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
                // id (primary key)
                $table->id();
                $table->unsignedBigInteger('category_id'); // categoryId
                $table->unsignedBigInteger('author_id'); // authorId
                // general attributes
                $table->string('title'); // title
                $table->string('article_image')->nullable(); // image (nullable in case an article may not have an image)
                $table->text('content')->comment('Corps de l\'article (HTML/Markdown)');
                $table->text('excerpt')->nullable()->comment('Extrait/résumé court');
                $table->string('slug')->unique(); // slug (unique)
                $table->enum('status', ['draft', 'published', 'archived', 'scheduled'])->default('draft'); // status with default value 'draft'
                $table->json('tags')->nullable()->comment('Tags flexibles ["prière", "jeunesse"]');
                // mise en avant
                $table->boolean('is_featured')->default(false)->comment('Article à la une');
                // Statistiques
                $table->unsignedInteger('shares_count')->default(0);
                $table->unsignedInteger('views_count')->default(0);
                $table->unsignedInteger('likes_count')->default(0);
                // Foreign keys (if categories and authors tables exist)
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                $table->foreign('author_id')->references('id')->on('admins')->onDelete('cascade');
                // other
                $table->softDeletes();
                $table->timestamps(); // Laravel's created_at and updated_at (optional, but recommended)
                $table->timestamp('published_at')->nullable()->comment('Date de publication effective ou programmée');
                // Index pour performance
                $table->index('slug');
                $table->index('category_id');
                $table->index('author_id');
                $table->index('status');
                $table->index('is_featured');
                $table->index(['status', 'published_at']);
                $table->fullText(['title', 'content'])->comment('Recherche plein texte');
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
