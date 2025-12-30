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
            $table->text('excerpt')->nullable()->comment('Résumé court');
            $table->string('actuality_image')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->enum('status', ['draft', 'published', 'archived', 'scheduled'])->default('draft');
             // Mise en avant
            $table->boolean('is_pinned')->default(false)->comment('Épinglée en haut');
            $table->integer('pin_order')->default(0)->comment('Ordre si plusieurs épinglées');
           // Statistiques
            $table->unsignedInteger('shares_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            // Foreign keys
            $table->foreignId('activity_id')->nullable()->constrained('activities')->onDelete('set null');
            $table->foreignId('author_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            // Timestamps and soft deletes
            $table->timestamps();
            $table->timestamp('published_at')->nullable();
            // Index pour performance
            $table->index('author_id');
            $table->index('slug');
            $table->index('status');
            $table->index('category_id');
            $table->index('is_pinned');
            $table->fullText(['title', 'content'])->comment('Recherche plein texte');
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
