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
        Schema::create('resource_activities', function (Blueprint $table) {
            $table->id();

            // Relation avec la table des activités
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');

            $table->string('title');
            $table->string('file_type'); // pdf, doc, image, video, etc.
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0); // en octets
            $table->string('mime_type')->nullable();
            $table->string('extension', 10)->nullable();
            // Statistiques
            $table->unsignedInteger('downloads_count')->default(0);
            // Optionnel : ajoute created_at et updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_activities');
    }
};
