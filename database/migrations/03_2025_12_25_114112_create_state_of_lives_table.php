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
        Schema::create('state_of_lives', function (Blueprint $table) {
            $table->id(); // +int id
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->string('name'); // +string name
            $table->string('slug')->unique(); // +string slug (unique est conseillé pour les slugs)
            $table->text('description')->nullable(); // +string description (text est mieux pour les descriptions longues)
            $table->text('short_description')->nullable()->comment('Résumé court pour cards');
            // Image
            $table->string('stateoflive_image')->nullable()->comment('Bannière page dédiée');
            // Critères d'appartenance (informationnel)
            $table->text('membership_criteria')->nullable()->comment('Ex: 18-35 ans pour Jeunes');
            $table->text('values')->nullable()->comment('Valeurs spécifiques du groupe');

            $table->enum('type', [
                'AGE_GROUP',        // Basé sur âge (Jeunes)
                'MARITAL_STATUS',   // État civil (Couples)
                'VOCATION',         // Vocation (Clercs, Laïcs)
                'CONSECRATION',     // Consécration (Consacrés H/F)
                'COMMITMENT',       // Niveau engagement (Engagés perpétuels)
                'FRATERNITY',       // Fraternité liée (Fraternité Sacerdotale)
                'RELATED_COMMUNITY' // Communauté issue de CANA (Frères, Sœurs)
            ])->nullable();

            $table->integer('members_count')->default(0)->comment('Nombre de membres (manuel ou auto)');
            $table->integer('contents_count')->default(0)->comment('Nombre de contenus publiés');
            $table->integer('page_views')->default(0);

            // +int responsableId
            // On assume qu'il pointe vers la table 'users'
             // Cles etrangeres
            $table->foreign('author_id', 'fk_stateoflive_author_new_unique_id')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('responsable_id', 'fk_stateoflive_reponsable_unique_123')->references('id')->on('admins')->onDelete('set null');

            $table->boolean('active')->default(true); // +boolean active
            $table->integer('ordre')->default(0); // +int ordre

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_of_lives');
    }
};
