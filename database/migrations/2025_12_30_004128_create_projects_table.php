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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            // Informations de base
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('objective')->nullable()->comment('Objectif du projet');
             // Financier
               // Dates
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            // Statut
            $table->enum('status', ['draft','active','completed', 'closed', 'canceled'])->default('draft');
            // Responsable
            $table->foreignId('responsible_id')
                  ->nullable()
                  ->constrained('admins')
                  ->onDelete('set null');
            $table->boolean('is_featured')->default(false);
            // Stats
            $table->timestamps();
            $table->softDeletes();

            $table->integer('donors_count')->default(0);
            $table->decimal('target_amount', 12, 2)->comment('Objectif financier');
            $table->decimal('raised_amount', 12, 2)->default(0)->comment('Montant collecté');
            $table->decimal('expenses_amount', 12, 2)->default(0)->comment('Montant dépensé');

            $table->index('slug');
            $table->index('status');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->foreign('project_id')->nullable()->references('id')->on('projects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
