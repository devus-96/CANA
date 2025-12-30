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
        Schema::create('daily_readings', function (Blueprint $table) {
            $table->id();
            $table->date('display_date')->unique()->comment('Date à laquelle la lecture est affichée');
            $table->string('verse', 500)->comment('Verset biblique principal');
            $table->text('meditation')->comment('Méditation/commentaire spirituel');
            $table->string('biblical_reference', 100)->comment('Référence biblique (ex: Jn 3:16-18)');
            $table->string('liturgical_category', 50)->nullable()
                  ->comment('Catégorie liturgique: Temps ordinaire, Avent, Carême, Pâques, etc.');
            $table->enum('status', ['draft', 'scheduled', 'published', 'archived'])->default('draft')->comment('Statut de publication');
            // Média optionnel (audio de la méditation)
            $table->string('audio_url')->nullable();
            $table->integer('audio_duration')->nullable()->comment('Durée en secondes');
            // foreign keys
            $table->foreignId('author_id')->nullable()->constrained('admins')->onDelete('set null')->comment('Auteur de la méditation');
            // Engagement
            $table->unsignedInteger('shares_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);

            $table->timestamps();
            // index
            $table->index('display_date');
            $table->index('status');
            $table->index(['display_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_readings');
    }
};
