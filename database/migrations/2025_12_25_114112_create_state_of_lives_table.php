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
            $table->string('name'); // +string name
            $table->string('slug')->unique(); // +string slug (unique est conseillé pour les slugs)
            $table->text('description')->nullable(); // +string description (text est mieux pour les descriptions longues)
            $table->string('image')->nullable(); // +string image (chemin vers le fichier)

            // +int responsableId
            // On assume qu'il pointe vers la table 'users'
            $table->foreignId('responsable_id')
                  ->nullable()
                  ->constrained('admins')
                  ->onDelete('set null');

            $table->boolean('active')->default(true); // +boolean active
            $table->integer('ordre')->default(0); // +int ordre

            $table->timestamps(); // Génère created_at et updated_at (standard Laravel)
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
