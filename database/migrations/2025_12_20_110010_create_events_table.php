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
            $table->integer('activity_id')->unsigned();
            $table->interger('location_id')->unsigned();
            $table->string('name');
            $table->string('description');
            $table->string('objectif');
            $table->string('type');
            $table->integer('max_capacity');
            $table->decimal('price');
            $table->string('image');
            $table->boolean('free');
            $table->boolean('recupent');
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
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
