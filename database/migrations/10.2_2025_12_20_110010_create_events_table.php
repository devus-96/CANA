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
            $table->string('image');
            $table->tinyInteger('status')->default(0); // 0=inactive,1=schedule,2=canceled.
            $table->boolean('is_free');
            $table->boolean('is_recurrent');

            // Cles etrangeres
            $table->foreignId('activity_id')->nullable()->constrained('activities')->onDelete('set null');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->foreignId('author')->nullable()->constrained('admins')->onDelete('set null');

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');

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
