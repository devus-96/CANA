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
        Schema::create('recurrence_rules', function (Blueprint $table) {
            $table->id();

            // Relation avec la table evenements
            // On suppose que la table s'appelle 'evenements'
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');

            $table->string('type_recurrence'); // ex: 'journalier', 'hebdomadaire', 'mensuel'
            $table->integer('interval')->default(1); // ex: toutes les '2' semaine

            $table->string('days_week')->nullable(); // ex: 'lundi,mercredi'
            $table->integer('day_of_the_month')->nullable(); // ex: le 15 du mois
            $table->integer('weeks_of_the_month')->nullable(); // ex: la 2ème semaine

            $table->text('exceptions')->nullable(); // Pour stocker des dates spécifiques exclues

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurrence_rules');
    }
};
