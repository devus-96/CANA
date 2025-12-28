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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('ticket_type');
            $table->integer('quantity');
            $table->json('participants');
            $table->decimal('price');
            $table->date('event_date');
            $table->tinyInteger('status')->default(0); // 0=pending,1=completed,2=failed.
            // Cles etrangeres
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->foreignId('event_id')->nullable()->constrained('events')->onDelete('set null');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');

            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('reservation_id')->nullable()->references('id')->on('reservations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

/*{
  "id": 892,
  "idEvenement": 45,
  "userId": "user_123",
  "ticketType": "STANDARD",
  "quantity": 3,
  "prix": 45000.00,
  "statut": "CONFIRMED",
  "dateReservation": "2025-12-21T14:30:22.000Z",
  "codeReservation": "CANA-20251221-AB7K9M",
  "nomComplet": "Jean Dupont",
  "email": "jean.dupont@email.com",
  "telephone": "+237677606169",
  "participants": [
    {
      "nom": "Jean Dupont",
      "type": "ADULTE",
      "ticketType": "STANDARD",
      "prix": 15000
    },
    {
      "nom": "Marie Dupont",
      "type": "ADULTE",
      "ticketType": "STANDARD",
      "prix": 15000
    },
    {
      "nom": "Pierre Dupont",
      "age": 10,
      "type": "ENFANT",
      "ticketType": "ENFANT",
      "prix": 15000
    }
  ]
}
*/
