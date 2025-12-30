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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->unsignedBigInteger('author')->nullable();
            $table->string('description');
            $table->string('name');
            $table->text('objectif');
            $table->string('activity_image');
            $table->boolean('active')->default(true);
            //
            $table->softDeletes();
            $table->timestamps();
            // Cles etrangeres
            $table->foreign('author_id', 'fk_activities_author_new_unique_id')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('responsable_id', 'fk_activities_author_unique_123')->references('id')->on('admins')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
