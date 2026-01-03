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
        Schema::create('event_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->date('date'); // Date spécifique
            $table->date('end_date')->nullable(); // nullable si la récurrence est infinie
            $table->time('start_time');
            $table->integer('available_spots');
            $table->unsignedInteger('reserved_spots')->default(0);
            // Pour gérer les exceptions (ex: ce lundi là est annulé)
            $table->boolean('is_cancelled')->default(false);
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null')->comment("le lieu de l'instance de l'evenement");

            $table->timestamps();

             // Un événement ne peut pas avoir deux fois la même date
            $table->unique(['event_id', 'date']);
            $table->index('event_id');
            $table->index('location_id');
        });

        Schema::table('event_subscriptions', function (Blueprint $table) {
            $table->foreign('event_instances_id')->nullable()->references('id')->on('event_instances')->onDelete('set null');
        });
         Schema::table('reservations', function (Blueprint $table) {
            $table->foreign('event_instances_id')->nullable()->references('id')->on('event_instances')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_instances');
    }
};
