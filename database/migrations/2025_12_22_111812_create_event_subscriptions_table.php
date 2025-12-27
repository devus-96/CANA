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
        Schema::create('event_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->unique();
                $table->string('email')->unique();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                //
                $table->timestamp('requested_at')->useCurrent();
                $table->foreignId('reviewed_by')->nullable()->constrained('admins')->onDelete('set null');
                $table->timestamp('reviewed_at')->nullable();
                // Cles etrangeres
                $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('cascade');
                $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
                // Empêche les doublons de souscription
                $table->unique(['member_id', 'event_id']);

                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_subscriptions');
    }
};
