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
            $table->string('description');
            $table->string('name');
            $table->text('objectif');
            $table->string('activity_image');
            $table->boolean('active')->default(true);

            $table->softDeletes();
            $table->timestamps();

            // Cles etrangeres
            $table->foreignId('author')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('responsable_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');

            $table->foreign('author')->references('id')->on('admin')->onDelete('cascade');
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
