<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use App\Models\Event;
use App\Models\Reservation;

class ReservationController extends Controller
{
    public function store (Request $request, Event $event)
    {
        $member = auth()->guard('menber')->user();

        Validator::make($request->all(), [
            'code'         => 'nullable|string',
            'ticket_type' => 'nullable|string',
            'quantity'    => 'required|integer|min:1',
            'price'        => 'required|numeric|min:0',
            'status'       => 'required|string',

            'method' => 'nullable|in:ORANGE_MONEY,MTN_MOMO',
            'phone_number' => 'nullable|string|max:20',
            'status' => 'nullable|in:PENDING,FAILED,SUCCEED',
        ]);

        if ($validator->fails()) {
            return response()->json(['statut' => 'error', 'message' => $validator->errors()], 422);
        }

        $alreadyReserved = Reservation::where('event_id', $event->id)
            ->whereDate('event_date', $eventDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->sum('quantity');

        $availableSpots = $event->max_capacity - $alreadyReserved;

        if ($quantity > $availableSpots) {
            return response()->json([
                'statut' => 'error',
                'message' => 'Places insuffisantes',
                'available' => $availableSpots,
                'requested' => $quantity
            ], 400);
        }

        if (!$event->is_free) {
            if (!$request->method) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Méthode de paiement requise pour les événements payants'
                ], 400);
            } else {

                $payment = Payment::create([
                    "user_id" => $user->id,
                    "amount" => $request->price,
                    "method" => $request->method,
                    'phone_number' => $request->phone_number,
                    "status" => NoCashPaymentProvider::STATUS_PENDING,
                ]);

                $methode = null;
                if(strtolower($request->method) == "orange")
                    $methode = NoCashPaymentProvider::OM_METHOD;
                else if(strtolower($request->method) == "mtn")
                    $methode = NoCashPaymentProvider::MOMO_METHOD;

                // initialize transaction
                $result = NoCashPaymentProvider::init($payment->id, $payment->phone_number, $payment->amount, $methode);

                 if($result["status"] == "success"){ // if success
                    // update transaction reference
                    $payment->update([
                        "reference"         => $result["data"],
                        'status'            => "1",
                    ]);

                    // return transaction reference
                    return response()->json(["data" => $result["data"]], Response::HTTP_OK);

                }else{ // else
                    // return error
                    return response()->json(["data" => "0", "status" => $result["status"], "message" => $result["message"]], 404); // Impossible d'initialiser le paiement
                }
            }
        }

        $code = Controller::generateReservationCode($member, $event);

        $reservation = $member->reservation()->create([
            'code'  => $code,
            'evenement_id'  => $event->id,
            'ticket_type'   => $request->ticket_type,
            'quantity'      => $resquest->quantity,
            'price'         => $request->price,
            'status'        => $request->status
        ]);

         return response()->json([
            'statut'  => 'success',
            'message' => 'Réservation créée avec succès',
            'data'    => $reservation,
        ], 201);

    }
}
