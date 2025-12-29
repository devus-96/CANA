<?php

namespace App\Http\Controllers\Reservation;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Event;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\EventSubscriptions;
use App\Services\NoCashPayment;
use App\Http\Controllers\Controller;
use App\Rules\PhoneNumber;
use App\Services\ReceiptGenerator;

class CreateReservation extends Controller
{
    public function __invoke (Request $request, Event $event)
    {
        /** @var \App\Models\Member $member */
        // Récupérer l'utilisateur authentifié
        $member = auth()->guard('members')->user();
        // Si l'utilisateur est authentifié, utiliser son ID
        $userId = $member ? $member->id : null;

        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:100',
            'phone'   => ['required', 'string', 'unique:'.Member::class, new PhoneNumber],
            'email'   => 'required|string|lowercase|email|max:255|unique:'.Member::class,
            'ticket_type'    => 'nullable|string',
            'quantity'       => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
            'event_date'     => 'required|date|after_or_equal:today',
            'participants'   => 'nullable|array|max:' . $request->input('quantity', 1),
            'participants.*.name' => 'required_with:participants|string|max:100',

            'method' => 'nullable|in:ORANGE_MONEY,MTN_MOMO',
            'status' => 'nullable|in:PENDING,FAILED,SUCCEED',
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }
        // si l'utilisateur est authentifié, vérifier son statut de souscription
        if ($member) {
            $subscription = EventSubscriptions::where('member_id', $member->id);
            if ($subscription->status === 'pending') {
                response()->json(['message' =>  'Votre demande de réservation est en attente de validation'], 403);
            } else if ($subscription->status === 'rejected') {
                response()->json(['message' =>  'Votre demande de réservation a été rejetée'], 403);
            }
            // si non recuperer la souscription avec le numero de telephone, puis verifier le status
        } else {
            $subscription = EventSubscriptions::where('phone', $request->phone)
                            ->where('email');

            if ($subscription->status === 'pending') {
                response()->json(['message' =>  'Votre demande de réservation est en attente de validation'], 403);
            } else if ($subscription->status === 'rejected') {
                response()->json(['message' =>  'Votre demande de réservation a été rejetée'], 403);
            }
        }
        // Vérifier la disponibilité des places
        $alreadyReserved = Reservation::where('event_id', $event->id)
                ->whereDate('event_date', $request->event_date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->sum('quantity');

        $availableSpots = $event->max_capacity - $alreadyReserved;
        // Si les places demandées dépassent les places disponibles, retourner une erreur
        if ($event->quantity > $availableSpots) {
            return response()->json([
                'statut' => 'error',
                'message' => 'Places insuffisantes',
                'available' => $availableSpots,
                'requested' => $event->quantity
            ], 400);
        }
        // Gérer le paiement pour les événements payants
        if (!$event->is_free) {
            if (!$request->method) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Méthode de paiement requise pour les événements payants'
                ], 400);
            } else {

                DB::beginTransaction();

                try {
                    // Create a new payment record
                    $payment = Payment::create([
                        "amount" => $request->price,
                        "method" => $request->method,
                        'phone' => $request->phone,
                        "status" => NoCashPayment::STATUS_PENDING,
                    ]);
                    // Determine the payment method
                    $methode = null;
                    if(strtolower($request->method) == "orange")
                        $methode = NoCashPayment::OM_METHOD;
                    else if(strtolower($request->method) == "mtn")
                        $methode = NoCashPayment::MOMO_METHOD;

                    // initialize transaction
                    $result = NoCashPayment::init($payment->id, $payment->phone, $payment->amount, $methode);

                    if($result["status"] == "success"){ // if success
                        // update transaction transaction_id
                        $payment->update([
                            "transaction_id"    => $result["data"],
                            'status'            => "0",
                        ]);
                        // Create the reservation
                        $code = Controller::generateReservationCode($member, $request->phone);

                        $reservation = Reservation::create([
                            "member_id" => $userId,
                            "name" => $request->name,
                            "email" => $request->email,
                            "phone" => $request->phone,
                            'code' => $code,
                            'event_id' => $event->id,
                            'ticket_type' => $request->ticket_type,
                            'quantity' => $request->quantity,
                            'price' => $request->price,
                            'event_date' => $request->event_date,
                            'payment_id' => $payment->id,
                            "participants" => $request->participants ? json_encode($request->participants) : null,
                        ]);

                        $payment->update(['reservation_id' => $reservation->id]);

                         DB::commit();
                        // return transaction reference
                        return response()->json([
                            "transaction" => $result["data"],
                            'statut'  => 'success',
                            'data'    => $reservation,
                            'message' => 'Réservation en cour de validation'

                        ], 200);

                    }else{ // else
                        DB::rollBack();
                        return response()->json(["data" => "0", "status" => $result["status"], "message" => $result["message"]], 404); // Impossible d'initialiser le paiement
                    }

                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['message' => 'failed', 'error' => $e->getMessage()], 500);
                }
            }
        // Si l'événement est gratuit, créer directement la réservation
        } else {
             $code = Controller::generateReservationCode($member, $request->phone);

             $reservation = Reservation::create([
                "user_id" => $userId,
                "name" => $request->name,
                "email" => $request->email,
                "phone" => $request->phone,
                'code' => $code,
                'event_id' => $event->id,
                'ticket_type' => $request->ticket_type,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'event_date' => $request->event_date,
                "participants" => $request->participants ? json_encode($request->participants) : null,
                'status' => 'active',
            ]);

             $userData = [
                'name' => $reservation->name,
                'email' => $reservation->email,
                'phone' => $reservation->phone
            ];

            $generator = new ReceiptGenerator($reservation,  $userData);

            $reservation->update(["status" => "1"]);

            return $generator->download();

        }
    }
}
