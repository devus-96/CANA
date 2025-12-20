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
            $table->id();

            // Relation avec la table des activités
            $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');

            $table->string('title');
            $table->string('file_type'); // ex: 'pdf', 'image/jpeg', 'video/mp4'
            $table->string('file_path'); // Chemin de stockage du fichier
            $table->bigInteger('file_size'); // Taille en octets

            // dateAdded : J'utilise le timestamp 'created_at' par défaut de Laravel
            // car il gère automatiquement le datetime d'ajout.
            $table->timestamp('date_added')->useCurrent();

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
