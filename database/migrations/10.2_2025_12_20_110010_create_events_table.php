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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('objectif');
            $table->string('type');
            $table->integer('max_capacity');
            $table->decimal('price');
            $table->string('event_image');
            $table->enum('status', ['inactive', 'canceled', 'active'])->default('inactive');
            $table->boolean('is_free');
            $table->boolean('is_recurrent');
            $table->date('start_date');
            $table->date('end_date')->nullable(); // nullable si la récurrence est infinie
            $table->time('start_time')->default('00:00:00');
            $table->time('end_time')->nullable();
            // Cles etrangeres
            $table->foreignId('activity_id')->nullable()->constrained('activities')->onDelete('set null');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null')->comment("le lieu par defaut de l'evenement, peut varier pour les instances");
            $table->foreignId('author_id')->nullable()->constrained('admins')->onDelete('set null');
            //
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
