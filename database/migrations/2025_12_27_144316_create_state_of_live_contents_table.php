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
        Schema::create('state_of_live_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_of_life_id')->constrained('states_of_life')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('admins')->onDelete('cascade');
            // Contenu
            $table->string('title');
            $table->text('content');
            $table->string('slug')->unique();
            $table->enum('type', [
                'ENSEIGNEMENT',  // Formations, cours, conférences
                'ACTUALITE',     // Nouvelles du groupe
                'TEMOIGNAGE',    // Partages d'expérience
                'ANNONCE'        // Informations ponctuelles
            ])->default('ENSEIGNEMENT');
            // Média attaché (audio/vidéo)
            $table->string('media_url')->nullable();
            $table->enum('media_type', ['AUDIO', 'VIDEO', 'DOCUMENT'])->nullable();
            $table->integer('media_duration')->nullable()->comment('Durée en secondes');
             // Image de couverture
            $table->string('featured_image')->nullable();
            // Publication
            $table->enum('status', [
                'DRAFT',         // Brouillon
                'PENDING',       // En attente validation admin (optionnel)
                'PUBLISHED',     // Publié
                'ARCHIVED'       // Archivé
            ])->default('DRAFT');
             $table->integer('views_count')->default(0);
             $table->boolean('is_featured')->default(false)->comment('Mis en avant sur la page');

             $table->timestamp('published_at')->nullable();

            // Ressources téléchargeables
            $table->json('attachments')->nullable()->comment('Fichiers PDF, DOCX à télécharger');

            // Commentaires (optionnel)
            $table->boolean('comments_enabled')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_of_live_contents');
    }
};
