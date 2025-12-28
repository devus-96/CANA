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
        Schema::create('medias', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique()->nullable();
                $table->string('title');
                $table->string('type'); // image, video, document, etc.
                $table->string('file_path');
                $table->string('file_name');
                $table->unsignedInteger('file_size')->default(0); // en octets
                $table->string('mime_type')->nullable();
                $table->string('extension')->nullable();
                $table->integer('duration')->nullable(); // en secondes pour audio/vidéo
                $table->boolean('is_public')->default(true);
                $table->enum('status', ['draft', 'published', 'private'])->default('published');
                // Statistiques
                $table->unsignedInteger('downloads_count')->default(0);
                $table->unsignedInteger('share_count')->default(0);
                $table->unsignedInteger('views_count')->default(0);
                // Clés étrangères
                $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
                $table->foreignId('author_id')->nullable()->constrained('admins')->onDelete('set null');
                $table->foreignId('activity_id')->nullable()->constrained('activities')->onDelete('set null');
                // Index
                $table->index('category_id');
                $table->index('author_id');
                $table->index('type');
                $table->index('created_at');

                $table->timestamps();
                $table->softDeletes(); // Pour la suppression douce
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
